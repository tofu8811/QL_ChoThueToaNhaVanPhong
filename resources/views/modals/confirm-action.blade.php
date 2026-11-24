<!-- MODAL: XÁC NHẬN HÀNH ĐỘNG ĐA TRẠNG THÁI (STATEFUL ACTION CONFIRMATION MODAL) -->
<style>
  @keyframes moSpin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
  }
  @keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
  }
  @keyframes modalScaleIn {
    from { opacity: 0; transform: scale(0.95); }
    to { opacity: 1; transform: scale(1); }
  }
  .mo-spinner {
    display: inline-block;
    width: 48px;
    height: 48px;
    border: 4px solid #e2e8f0;
    border-top-color: var(--mo-primary, #0284c7);
    border-radius: 50%;
    animation: moSpin 0.75s linear infinite !important;
  }
</style>
<div id="modal-confirm-action" class="mo-modal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(15,23,42,0.65);backdrop-filter:blur(4px);z-index:99999;align-items:center;justify-content:center;transition:all 0.2s ease;">
  <div style="background:#ffffff;border-radius:14px;width:480px;max-width:92vw;box-shadow:0 25px 50px -12px rgba(0,0,0,0.35);overflow:hidden;border:1px solid #cbd5e1;display:flex;flex-direction:column;position:relative;animation:modalScaleIn 0.2s cubic-bezier(0.16, 1, 0.3, 1);">

    <!-- Nút X đóng góc trên phải (chỉ hiện khi ở CONFIRM hoặc FAILED) -->
    <button type="button" id="confirm-modal-close-x" onclick="moCloseConfirmAction()" style="position:absolute;top:12px;right:14px;background:none;border:none;color:#94a3b8;font-size:22px;line-height:1;cursor:pointer;z-index:2;padding:4px;transition:color 0.15s;" onmouseover="this.style.color='#334155'" onmouseout="this.style.color='#94a3b8'">&times;</button>

    <!-- TRẠNG THÁI 1: CHỜ XÁC NHẬN (CONFIRM PROMPT) -->
    <div id="confirm-state-prompt" style="padding:28px 24px 22px;display:flex;flex-direction:column;align-items:center;text-align:center;">
      <!-- Dynamic Icon Badge -->
      <div id="confirm-modal-icon-badge" style="width:56px;height:56px;border-radius:50%;background:#fee2e2;color:#dc2626;display:flex;align-items:center;justify-content:center;margin-bottom:14px;font-size:24px;box-shadow:0 0 0 6px #fef2f2;">
        <i id="confirm-modal-icon" class="fa-solid fa-trash-can"></i>
      </div>

      <!-- Action Title -->
      <h3 id="confirm-modal-title" style="margin:0 0 6px;font-size:17px;font-weight:700;color:#0f172a;">Xác Nhận Thao Tác</h3>

      <!-- Target Item Card (Pill) -->
      <div id="confirm-modal-target-wrap" style="display:none;width:100%;margin:6px 0 12px;background:#f8fafc;border:1px dashed #cbd5e1;border-radius:8px;padding:9px 14px;box-sizing:border-box;">
        <span id="confirm-modal-target-text" style="font-size:13.5px;font-weight:700;color:#1e293b;word-break:break-word;"></span>
      </div>

      <!-- Main Message -->
      <p id="confirm-modal-message" style="margin:0 0 10px;font-size:13.5px;color:#475569;line-height:1.5;"></p>

      <!-- Note / Warning Callout -->
      <div id="confirm-modal-note-wrap" style="display:none;width:100%;background:#fffbeb;border:1px solid #fde68a;border-radius:8px;padding:10px 12px;margin-bottom:16px;font-size:12.5px;color:#92400e;line-height:1.45;text-align:left;box-sizing:border-box;gap:8px;align-items:flex-start;">
        <i class="fa-solid fa-circle-info" style="color:#f59e0b;font-size:15px;margin-top:2px;flex-shrink:0;"></i>
        <span id="confirm-modal-note-text"></span>
      </div>

      <!-- Action Buttons -->
      <div style="display:flex;gap:10px;width:100%;margin-top:4px;">
        <button type="button" id="confirm-modal-btn-cancel" onclick="moCloseConfirmAction()" class="btn-mo-outline" style="flex:1;padding:9px 16px;font-size:13.5px;font-weight:600;border-radius:8px;border:1px solid #cbd5e1;background:#ffffff;color:#475569;cursor:pointer;transition:all 0.15s;display:inline-flex;align-items:center;justify-content:center;text-align:center;">Hủy bỏ</button>
        <button type="button" id="confirm-modal-btn-action" onclick="moExecuteConfirmAction()" style="flex:1;padding:9px 16px;font-size:13.5px;font-weight:600;border-radius:8px;border:none;background:#dc2626;color:#ffffff;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;gap:6px;box-shadow:0 2px 4px rgba(220,38,38,0.25);transition:all 0.15s;text-align:center;">
          <i id="confirm-modal-btn-icon" class="fa-solid fa-trash-can"></i>
          <span id="confirm-modal-btn-action-text">Xóa</span>
        </button>
      </div>
    </div>

    <!-- TRẠNG THÁI 2: ĐANG XỬ LÝ (PROCESSING) -->
    <div id="confirm-state-processing" style="display:none;flex-direction:column;align-items:center;justify-content:center;padding:36px 24px 32px;text-align:center;">
      <div class="mo-spinner" style="width:48px;height:48px;border:4px solid #e2e8f0;border-top-color:var(--mo-primary,#0284c7);border-radius:50%;animation:moSpin 0.75s linear infinite;margin-bottom:16px;"></div>
      <h4 id="confirm-modal-processing-title" style="margin:0 0 6px;font-size:16px;font-weight:700;color:#0f172a;">Đang xử lý yêu cầu...</h4>
      <p id="confirm-modal-processing-message" style="margin:0;font-size:13px;color:#64748b;line-height:1.5;">Hệ thống đang kiểm tra ràng buộc và thực thi thao tác.<br>Vui lòng đợi trong giây lát.</p>
    </div>

    <!-- TRẠNG THÁI 3: THÀNH CÔNG (SUCCESS) -->
    <div id="confirm-state-success" style="display:none;flex-direction:column;align-items:center;justify-content:center;padding:30px 24px 24px;text-align:center;">
      <div style="width:56px;height:56px;border-radius:50%;background:#dcfce7;color:#16a34a;display:flex;align-items:center;justify-content:center;margin-bottom:14px;font-size:26px;box-shadow:0 0 0 6px #f0fdf4;">
        <i class="fa-solid fa-check"></i>
      </div>
      <h4 style="margin:0 0 6px;font-size:16px;font-weight:700;color:#15803d;">Thao tác thành công!</h4>
      <p id="confirm-modal-success-message" style="margin:0 0 16px;font-size:13.5px;color:#334155;line-height:1.5;word-break:break-word;"></p>
      
      <!-- Auto dismiss countdown bar -->
      <div style="width:100%;background:#e2e8f0;height:4px;border-radius:99px;overflow:hidden;margin-bottom:14px;">
        <div id="confirm-modal-success-progress" style="width:100%;height:100%;background:#16a34a;transition:width 1.5s linear;"></div>
      </div>
      
      <button type="button" onclick="moDismissSuccessConfirmAction()" class="btn-mo-outline" style="padding:7px 24px;font-size:13px;font-weight:600;border-radius:6px;border:1px solid #cbd5e1;background:#ffffff;color:#334155;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;text-align:center;">Đóng ngay</button>
    </div>

    <!-- TRẠNG THÁI 4: THẤT BẠI / VI PHẠM RÀNG BUỘC (FAILED) -->
    <div id="confirm-state-failed" style="display:none;flex-direction:column;align-items:center;justify-content:center;padding:28px 24px 22px;text-align:center;">
      <div style="width:56px;height:56px;border-radius:50%;background:#fee2e2;color:#dc2626;display:flex;align-items:center;justify-content:center;margin-bottom:14px;font-size:26px;box-shadow:0 0 0 6px #fef2f2;">
        <i class="fa-solid fa-triangle-exclamation"></i>
      </div>
      <h4 style="margin:0 0 6px;font-size:16px;font-weight:700;color:#b91c1c;">Không thể hoàn tất thao tác!</h4>
      
      <!-- Error Message Callout Box -->
      <div style="width:100%;background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:12px 14px;margin-bottom:16px;text-align:left;box-sizing:border-box;">
        <div style="font-size:11.5px;font-weight:700;color:#991b1b;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:4px;display:flex;align-items:center;gap:5px;">
          <i class="fa-solid fa-circle-exclamation"></i> Lý do từ hệ thống:
        </div>
        <div id="confirm-modal-failed-message" style="font-size:13px;color:#7f1d1d;line-height:1.5;word-break:break-word;"></div>
      </div>

      <div style="display:flex;gap:10px;width:100%;">
        <button type="button" onclick="moCloseConfirmAction()" class="btn-mo-outline" style="flex:1;padding:9px 16px;font-size:13.5px;font-weight:600;border-radius:8px;border:1px solid #cbd5e1;background:#ffffff;color:#475569;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;text-align:center;">Đóng</button>
        <button type="button" id="confirm-modal-btn-retry" onclick="moRetryConfirmAction()" style="flex:1;padding:9px 16px;font-size:13.5px;font-weight:600;border-radius:8px;border:none;background:#2563eb;color:#ffffff;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;gap:6px;transition:all 0.15s;text-align:center;">
          <i class="fa-solid fa-rotate-right"></i> Thử lại
        </button>
      </div>
    </div>

  </div>
</div>
