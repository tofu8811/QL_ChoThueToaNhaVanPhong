<?php

namespace App\Services;

use App\Enums\TicketCategory;
use App\Enums\TicketPriority;
use App\Enums\TicketProofType;
use App\Enums\TicketStatus;
use App\Events\TicketCreatedEvent;
use App\Exceptions\BusinessException;
use App\Models\Admin;
use App\Models\Contract;
use App\Models\PropertyManager;
use App\Models\Tenant;
use App\Models\Ticket;
use App\Models\TicketHistory;
use App\Models\TicketProofImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Service tầng nghiệp vụ xử lý Yêu cầu hỗ trợ & Sự cố bảo trì (Tickets & Maintenance).
 */
class TicketService
{
    /**
     * 57. Lấy danh sách phiếu yêu cầu hỗ trợ / bảo trì (Tickets).
     * Phân quyền: Tenant xem ticket công ty mình, PM xem tòa nhà phụ trách, Admin xem toàn bộ.
     *
     * @param array $filters
     * @param mixed $user
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getTicketList(array $filters, mixed $user, int $perPage = 10)
    {
        $query = Ticket::with([
            'building:id,name,building_code,address',
            'unit:id,unit_code,floor_number',
            'tenant:id,company_name,representative_name,contact_phone',
            'assignedPm:id,full_name,employee_code,phone',
        ]);

        // Phân quyền dữ liệu (Multi-guard scope)
        if ($user instanceof Tenant) {
            $query->where('tenant_id', $user->id);
        } elseif ($user instanceof PropertyManager) {
            $managedBuildingIds = DB::table('manager_buildings')
                ->where('pm_id', $user->id)
                ->pluck('building_id')
                ->toArray();
            $query->whereIn('building_id', $managedBuildingIds);
        }

        // Lọc theo trạng thái
        if (!empty($filters['status'])) {
            $status = strtoupper($filters['status']);
            $statusMap = [
                'OPEN' => 'NEW',
                'RESOLVED' => 'WAITING_CONFIRMATION',
                'CLOSED' => 'COMPLETED',
            ];
            $dbStatus = $statusMap[$status] ?? $status;
            $query->where('status', $dbStatus);
        }

        // Lọc theo mức độ ưu tiên
        if (!empty($filters['priority'])) {
            $query->where('priority', strtoupper($filters['priority']));
        }

        // Lọc theo phân loại sự cố
        if (!empty($filters['category'])) {
            $query->where('category', strtoupper($filters['category']));
        }

        // Lọc theo tòa nhà
        if (!empty($filters['building_id'])) {
            $query->where('building_id', $filters['building_id']);
        }

        // Tìm kiếm theo từ khóa
        if (!empty($filters['keyword'])) {
            $kw = '%' . trim($filters['keyword']) . '%';
            $query->where(function ($q) use ($kw) {
                $q->where('ticket_code', 'like', $kw)
                  ->orWhere('title', 'like', $kw)
                  ->orWhere('description', 'like', $kw);
            });
        }

        return $query->latest()->paginate($perPage);
    }

    /**
     * Khách thuê gửi yêu cầu hỗ trợ sự cố mới từ Tenant Portal (UC-04).
     *
     * @param array $data
     * @param Tenant $tenant
     * @return Ticket
     * @throws \Throwable
     */
    public function createTenantTicket(array $data, Tenant $tenant): Ticket
    {
        return DB::transaction(function () use ($data, $tenant) {
            // 1. Tự động sinh mã ticket duy nhất theo định dạng TK-YYYY-XXXX
            $year = date('Y');
            $prefix = "TK-{$year}-";
            $latest = Ticket::where('ticket_code', 'like', "{$prefix}%")
                ->orderBy('ticket_code', 'desc')
                ->lockForUpdate()
                ->value('ticket_code');

            $seq = 1;
            if ($latest && preg_match('/-(\d+)$/', $latest, $matches)) {
                $seq = ((int) $matches[1]) + 1;
            }
            $ticketCode = $prefix . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);

            // 2. Tự động tìm hợp đồng hiệu lực nếu không truyền contract_id
            $contractId = $data['contract_id'] ?? null;
            if (!$contractId) {
                $contractId = Contract::where('tenant_id', $tenant->id)
                    ->where('building_id', $data['building_id'])
                    ->whereIn('status', ['ACTIVE', 'RENEWED'])
                    ->value('id');
            }

            // 3. Khởi tạo bản ghi Ticket
            $ticket = Ticket::create([
                'ticket_code'        => $ticketCode,
                'building_id'        => $data['building_id'],
                'creator_role'       => 'TENANT',
                'creator_tenant_id'  => $tenant->id,
                'tenant_id'          => $tenant->id,
                'contract_id'        => $contractId,
                'unit_id'            => $data['unit_id'] ?? null,
                'category'           => $data['category'],
                'priority'           => $data['priority'] ?? TicketPriority::NORMAL->value,
                'title'              => $data['title'],
                'description'        => $data['description'],
                'status'             => TicketStatus::NEW,
            ]);

            // 4. Ghi nhận mốc lịch sử đầu tiên
            $history = TicketHistory::create([
                'ticket_id'       => $ticket->id,
                'actor_type'      => 'TENANT',
                'tenant_actor_id' => $tenant->id,
                'from_status'     => null,
                'to_status'       => TicketStatus::NEW,
                'message'         => 'Khách thuê gửi yêu cầu hỗ trợ mới.',
            ]);

            // 5. Lưu ảnh hiện trạng đính kèm (nếu có)
            if (!empty($data['images']) && is_array($data['images'])) {
                foreach ($data['images'] as $imageItem) {
                    $url = $this->storeProofImageFile($ticket->id, $imageItem);
                    if ($url) {
                        TicketProofImage::create([
                            'ticket_id'              => $ticket->id,
                            'ticket_history_id'      => $history->id,
                            'image_url'              => $url,
                            'proof_type'             => TicketProofType::INCIDENT->value,
                            'caption'                => 'Ảnh hiện trường sự cố ban đầu',
                            'uploaded_by_actor_type' => 'TENANT',
                        ]);
                    }
                }
            }

            // 6. Phát sóng sự kiện WebSocket Reverb đến BQL tòa nhà
            try {
                TicketCreatedEvent::dispatch($ticket);
            } catch (\Throwable $e) {
                Log::warning("WebSocket broadcast failed for ticket {$ticket->ticket_code}: " . $e->getMessage());
            }

            return $ticket->load(['building', 'unit', 'tenant', 'histories', 'proofImages']);
        });
    }

    /**
     * Xem chi tiết tiến trình xử lý Ticket & Lịch sử chuyển trạng thái.
     *
     * @param string $id
     * @param mixed $user
     * @return Ticket
     * @throws BusinessException
     */
    public function getTicketDetails(string $id, mixed $user): Ticket
    {
        $ticket = Ticket::with([
            'building',
            'unit',
            'tenant',
            'assignedPm',
            'creatorTenant',
            'creatorPm',
            'creatorAdmin',
            'histories.tenantActor',
            'histories.pmActor',
            'histories.salesActor',
            'histories.adminActor',
            'histories.proofImages',
            'proofImages',
        ])->findOrFail($id);

        // Khách thuê chỉ được xem ticket do chính công ty mình tạo hoặc là đối tượng chịu ảnh hưởng
        if ($user instanceof Tenant && $ticket->tenant_id !== $user->id && $ticket->creator_tenant_id !== $user->id) {
            throw new BusinessException('Bạn không có quyền truy cập phiếu yêu cầu hỗ trợ này.', 403);
        }

        return $ticket;
    }

    /**
     * Cập nhật thông tin chi tiết ticket (khi ticket còn ở trạng thái Mới OPEN / NEW).
     *
     * @param string $id
     * @param array $data
     * @param mixed $user
     * @return Ticket
     * @throws BusinessException
     */
    public function updateTicket(string $id, array $data, mixed $user): Ticket
    {
        $ticket = Ticket::findOrFail($id);

        if ($user instanceof Tenant && $ticket->tenant_id !== $user->id && $ticket->creator_tenant_id !== $user->id) {
            throw new BusinessException('Bạn không có quyền chỉnh sửa phiếu yêu cầu hỗ trợ này.', 403);
        }

        $currentStatus = $ticket->status instanceof \BackedEnum ? $ticket->status->value : (string) $ticket->status;
        if ($currentStatus !== TicketStatus::NEW->value) {
            throw new BusinessException('Chỉ có thể cập nhật thông tin khi Ticket còn ở trạng thái Mới (OPEN).', 422);
        }

        $ticket->fill(array_filter([
            'title'       => $data['title'] ?? null,
            'description' => $data['description'] ?? null,
            'category'    => $data['category'] ?? null,
            'priority'    => $data['priority'] ?? null,
            'unit_id'     => array_key_exists('unit_id', $data) ? $data['unit_id'] : $ticket->unit_id,
        ], fn ($val) => !is_null($val)));

        $ticket->save();

        return $ticket;
    }

    /**
     * Xóa mềm ticket (chỉ áp dụng cho Quản trị viên Admin).
     *
     * @param string $id
     * @param mixed $user
     * @return bool
     * @throws BusinessException
     */
    public function deleteTicket(string $id, mixed $user): bool
    {
        if (!($user instanceof Admin)) {
            throw new BusinessException('Chỉ Quản trị viên (Admin) mới có quyền xóa phiếu yêu cầu hỗ trợ.', 403);
        }

        $ticket = Ticket::findOrFail($id);
        return (bool) $ticket->delete();
    }

    /**
     * Ban quản lý tiếp nhận và phân công nhân viên kỹ thuật trực tiếp xử lý sự cố.
     * Chuyển trạng thái sang IN_PROGRESS.
     *
     * @param string $id
     * @param array $data
     * @param mixed $user
     * @return Ticket
     * @throws BusinessException
     * @throws \Throwable
     */
    public function assignTicket(string $id, array $data, mixed $user): Ticket
    {
        if (!($user instanceof Admin) && !($user instanceof PropertyManager)) {
            throw new BusinessException('Chỉ Ban quản lý hoặc Quản trị viên mới có quyền tiếp nhận và phân công xử lý.', 403);
        }

        return DB::transaction(function () use ($id, $data, $user) {
            $ticket = Ticket::lockForUpdate()->findOrFail($id);
            $fromStatus = $ticket->status instanceof \BackedEnum ? $ticket->status : TicketStatus::from($ticket->status);

            $ticket->assigned_pm_id = $data['assigned_pm_id'];
            $ticket->status = TicketStatus::IN_PROGRESS;

            if (!empty($data['expected_completion_at'])) {
                $ticket->expected_completion_at = $data['expected_completion_at'];
            }
            $ticket->save();

            // Ghi nhận lịch sử chuyển trạng thái
            TicketHistory::create([
                'ticket_id'       => $ticket->id,
                'actor_type'      => $user instanceof Admin ? 'ADMIN' : 'PM',
                'admin_actor_id'  => $user instanceof Admin ? $user->id : null,
                'pm_actor_id'     => $user instanceof PropertyManager ? $user->id : null,
                'from_status'     => $fromStatus,
                'to_status'       => TicketStatus::IN_PROGRESS,
                'message'         => $data['note'] ?? 'Ban quản lý đã tiếp nhận và phân công nhân viên kỹ thuật xử lý sự cố.',
            ]);

            return $ticket->load(['assignedPm', 'histories']);
        });
    }

    /**
     * Cập nhật tiến độ xử lý Ticket: ON_HOLD hoặc RESOLVED / WAITING_CONFIRMATION.
     *
     * @param string $id
     * @param array $data
     * @param mixed $user
     * @return Ticket
     * @throws BusinessException
     * @throws \Throwable
     */
    public function updateTicketStatus(string $id, array $data, mixed $user): Ticket
    {
        if (!($user instanceof Admin) && !($user instanceof PropertyManager)) {
            throw new BusinessException('Chỉ Ban quản lý hoặc Quản trị viên mới có quyền cập nhật tiến độ xử lý.', 403);
        }

        return DB::transaction(function () use ($id, $data, $user) {
            $ticket = Ticket::lockForUpdate()->findOrFail($id);
            $fromStatus = $ticket->status instanceof \BackedEnum ? $ticket->status : TicketStatus::from($ticket->status);

            $rawStatus = strtoupper(trim($data['status']));
            $toStatus = match ($rawStatus) {
                'RESOLVED', 'WAITING_CONFIRMATION' => TicketStatus::WAITING_CONFIRMATION,
                'ON_HOLD' => TicketStatus::ON_HOLD,
                'IN_PROGRESS' => TicketStatus::IN_PROGRESS,
                default => TicketStatus::from($rawStatus),
            };

            $ticket->status = $toStatus;

            if ($toStatus === TicketStatus::ON_HOLD) {
                $ticket->on_hold_reason = $data['on_hold_reason'] ?? null;
            } elseif ($toStatus === TicketStatus::WAITING_CONFIRMATION) {
                $ticket->resolution_summary = $data['resolution_summary'] ?? null;
                $ticket->resolved_at = now();
            }

            $ticket->save();

            $message = $data['note'] ?? match ($toStatus) {
                TicketStatus::WAITING_CONFIRMATION => 'Kỹ thuật viên đã xử lý xong sự cố, lập biên bản chờ khách thuê nghiệm thu.',
                TicketStatus::ON_HOLD => 'Tạm hoãn tiến độ do: ' . ($data['on_hold_reason'] ?? 'chờ vật tư'),
                default => 'Cập nhật tiến độ xử lý ticket.',
            };

            TicketHistory::create([
                'ticket_id'       => $ticket->id,
                'actor_type'      => $user instanceof Admin ? 'ADMIN' : 'PM',
                'admin_actor_id'  => $user instanceof Admin ? $user->id : null,
                'pm_actor_id'     => $user instanceof PropertyManager ? $user->id : null,
                'from_status'     => $fromStatus,
                'to_status'       => $toStatus,
                'message'         => $message,
            ]);

            return $ticket->load(['assignedPm', 'histories']);
        });
    }

    /**
     * Tải lên hình ảnh bằng chứng nghiệm thu sự cố (Before / After).
     *
     * @param string $id
     * @param array $data
     * @param mixed $user
     * @return array<TicketProofImage>
     * @throws BusinessException
     */
    public function uploadProofImages(string $id, array $data, mixed $user): array
    {
        $ticket = Ticket::findOrFail($id);

        $actorType = match (true) {
            $user instanceof Tenant          => 'TENANT',
            $user instanceof PropertyManager => 'PM',
            $user instanceof Admin           => 'ADMIN',
            default                          => 'PM',
        };

        if ($user instanceof Tenant && $ticket->tenant_id !== $user->id && $ticket->creator_tenant_id !== $user->id) {
            throw new BusinessException('Bạn không có quyền tải ảnh cho phiếu yêu cầu hỗ trợ này.', 403);
        }

        $proofType = TicketProofType::normalize($data['proof_type']);
        $caption = $data['caption'] ?? null;
        $images = $data['images'] ?? [];
        $uploaded = [];

        foreach ($images as $img) {
            $url = $this->storeProofImageFile($ticket->id, $img);
            if ($url) {
                $proof = TicketProofImage::create([
                    'ticket_id'              => $ticket->id,
                    'ticket_history_id'      => null,
                    'image_url'              => $url,
                    'proof_type'             => $proofType,
                    'caption'                => $caption,
                    'uploaded_by_actor_type' => $actorType,
                ]);
                $uploaded[] = $proof;
            }
        }

        return $uploaded;
    }

    /**
     * Khách thuê xác nhận nghiệm thu kết quả xử lý (Confirm / Reject).
     *
     * @param string $id
     * @param array $data
     * @param Tenant $tenant
     * @return Ticket
     * @throws BusinessException
     * @throws \Throwable
     */
    public function confirmTicket(string $id, array $data, Tenant $tenant): Ticket
    {
        return DB::transaction(function () use ($id, $data, $tenant) {
            $ticket = Ticket::lockForUpdate()->findOrFail($id);

            if ($ticket->tenant_id !== $tenant->id && $ticket->creator_tenant_id !== $tenant->id) {
                throw new BusinessException('Bạn không có quyền nghiệm thu phiếu yêu cầu này.', 403);
            }

            $fromStatus = $ticket->status instanceof \BackedEnum ? $ticket->status : TicketStatus::from($ticket->status);
            $confirmed = filter_var($data['confirmed'], FILTER_VALIDATE_BOOLEAN);

            if ($confirmed) {
                $toStatus = TicketStatus::COMPLETED;
                $ticket->status = $toStatus;
                $ticket->completed_at = now();
                $message = $data['feedback'] ?? 'Khách thuê đã xác nhận nghiệm thu hoàn tất kết quả sửa chữa.';
            } else {
                $toStatus = TicketStatus::IN_PROGRESS;
                $ticket->status = $toStatus;
                $message = 'Khách thuê từ chối nghiệm thu: ' . ($data['feedback'] ?? 'Chưa đạt yêu cầu, cần khắc phục thêm.');
            }

            $ticket->save();

            TicketHistory::create([
                'ticket_id'       => $ticket->id,
                'actor_type'      => 'TENANT',
                'tenant_actor_id' => $tenant->id,
                'from_status'     => $fromStatus,
                'to_status'       => $toStatus,
                'message'         => $message,
            ]);

            return $ticket->load(['histories']);
        });
    }

    /**
     * Khách thuê đánh giá chất lượng phục vụ của Ban quản lý (1-5 sao).
     *
     * @param string $id
     * @param array $data
     * @param Tenant $tenant
     * @return Ticket
     * @throws BusinessException
     */
    public function rateTicket(string $id, array $data, Tenant $tenant): Ticket
    {
        $ticket = Ticket::findOrFail($id);

        if ($ticket->tenant_id !== $tenant->id && $ticket->creator_tenant_id !== $tenant->id) {
            throw new BusinessException('Bạn không có quyền đánh giá phiếu yêu cầu này.', 403);
        }

        $ticket->tenant_rating = (int) $data['rating'];
        $ticket->tenant_feedback = $data['feedback'] ?? null;
        $ticket->save();

        return $ticket;
    }

    /**
     * Lưu trữ file ảnh lên Supabase hoặc Local Storage.
     *
     * @param string $ticketId
     * @param mixed $fileItem
     * @return string|null
     */
    protected function storeProofImageFile(string $ticketId, mixed $fileItem): ?string
    {
        if (is_string($fileItem) && filter_var($fileItem, FILTER_VALIDATE_URL)) {
            return $fileItem;
        }

        if ($fileItem instanceof UploadedFile) {
            $filename = Str::uuid()->toString() . '.' . $fileItem->getClientOriginalExtension();
            $path = "tickets/{$ticketId}/{$filename}";

            try {
                if (config('filesystems.disks.supabase.key')) {
                    Storage::disk('supabase')->put($path, file_get_contents($fileItem));
                    return Storage::disk('supabase')->url($path);
                }
            } catch (\Throwable $e) {
                Log::warning("Supabase upload failed for ticket proof: " . $e->getMessage());
            }

            $localPath = $fileItem->storeAs("public/tickets/{$ticketId}", $filename);
            return Storage::url($localPath);
        }

        return null;
    }
}
