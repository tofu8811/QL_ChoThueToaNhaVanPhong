<?php

namespace Tests\Unit\Enums;

use App\Enums\ContractStatus;
use App\Enums\DebitNoteStatus;
use App\Enums\LeadStatus;
use App\Enums\TicketStatus;
use App\Enums\UnitStatus;
use PHPUnit\Framework\TestCase;

class StatusEnumTest extends TestCase
{
    public function test_unit_status_enum(): void
    {
        $this->assertSame('AVAILABLE', UnitStatus::AVAILABLE->value);
        $this->assertSame('Đang trống', UnitStatus::AVAILABLE->label());
        $this->assertTrue(UnitStatus::AVAILABLE->canBeLeased());
        $this->assertFalse(UnitStatus::LEASED->canBeLeased());
        $this->assertCount(4, UnitStatus::cases());
        $this->assertArrayHasKey('MAINTENANCE', UnitStatus::options());
    }

    public function test_lead_status_enum(): void
    {
        $this->assertSame('NEW', LeadStatus::NEW->value);
        $this->assertSame('Mới tiếp nhận', LeadStatus::NEW->label());
        $this->assertFalse(LeadStatus::NEW->isClosed());
        $this->assertTrue(LeadStatus::WON->isClosed());
        $this->assertTrue(LeadStatus::LOST->isClosed());
        $this->assertCount(5, LeadStatus::cases());
    }

    public function test_contract_status_enum(): void
    {
        $this->assertSame('ACTIVE', ContractStatus::ACTIVE->value);
        $this->assertSame('Đang hiệu lực', ContractStatus::ACTIVE->label());
        $this->assertTrue(ContractStatus::ACTIVE->isEffective());
        $this->assertTrue(ContractStatus::ACTIVE->canGenerateInvoice());
        $this->assertFalse(ContractStatus::DRAFT->isEffective());
        $this->assertFalse(ContractStatus::TERMINATED->isEffective());
        $this->assertCount(5, ContractStatus::cases());
    }

    public function test_ticket_status_enum(): void
    {
        $this->assertSame('IN_PROGRESS', TicketStatus::IN_PROGRESS->value);
        $this->assertTrue(TicketStatus::IN_PROGRESS->isOpen());
        $this->assertTrue(TicketStatus::IN_PROGRESS->canSubmitCompletion());
        $this->assertFalse(TicketStatus::COMPLETED->isOpen());
        $this->assertFalse(TicketStatus::CANCELLED->isOpen());
        $this->assertCount(6, TicketStatus::cases());
    }

    public function test_debit_note_status_enum(): void
    {
        $this->assertSame('DRAFT', DebitNoteStatus::DRAFT->value);
        $this->assertTrue(DebitNoteStatus::DRAFT->isEditable());
        $this->assertFalse(DebitNoteStatus::ISSUED->isEditable());
        $this->assertTrue(DebitNoteStatus::ISSUED->isPayable());
        $this->assertTrue(DebitNoteStatus::OVERDUE->isPayable());
        $this->assertFalse(DebitNoteStatus::PAID->isPayable());
        $this->assertCount(7, DebitNoteStatus::cases());
    }
}
