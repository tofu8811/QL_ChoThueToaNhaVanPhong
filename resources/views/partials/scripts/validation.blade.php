<script>
/**
 * ==============================================================================
 * THƯ VIỆN TIỆN ÍCH VALIDATION THỜI GIAN THỰC TOÀN HỆ THỐNG (BMS VALIDATION ENGINE)
 * Phản hồi tức thì 0ms, chống giật layout (Zero Layout Shift), tái sử dụng 100%
 * ==============================================================================
 */

// 1. Quản lý trạng thái lỗi trên giao diện (Visual Error Slots - Zero Layout Shift)
function moSetFieldError(inputId, slotId, msg) {
  const input = document.getElementById(inputId);
  const slot = document.getElementById(slotId);
  if (input) {
    input.classList.add('input-error');
    input.classList.add('is-invalid');
  }
  if (slot) {
    slot.style.display = 'flex';
    slot.innerHTML = `<span>${msg}</span>`;
  }
}

function moClearFieldError(inputId, slotId) {
  const input = document.getElementById(inputId);
  const slot = document.getElementById(slotId);
  if (input) {
    input.classList.remove('input-error');
    input.classList.remove('is-invalid');
  }
  if (slot) {
    slot.innerHTML = '';
  }
}

// 2. Làm sạch & Chuẩn hóa dữ liệu đầu vào (Input Sanitizers)
function moSanitizeNumeric(el) {
  if (!el) return '';
  el.value = el.value.replace(/[^0-9]/g, '');
  return el.value;
}

function moSanitizeDecimal(el) {
  if (!el) return '';
  el.value = el.value.replace(/[^0-9.]/g, '');
  // Chỉ cho phép 1 dấu chấm thập phân duy nhất
  const parts = el.value.split('.');
  if (parts.length > 2) {
    el.value = parts[0] + '.' + parts.slice(1).join('');
  }
  return el.value;
}

function moFormatNumber(num) {
  if (isNaN(num) || num === null || num === '') return '0';
  return Number(num).toLocaleString('vi-VN');
}

// 3. Quy tắc kiểm thực dữ liệu chuẩn (Validation Rules 0ms)
function moValidatePhone(val) {
  let raw = (val || '').trim();
  if (raw.startsWith('+84')) {
    raw = '0' + raw.slice(3);
  } else if (raw.startsWith('84') && raw.length >= 11) {
    raw = '0' + raw.slice(2);
  }
  const clean = raw.replace(/[^0-9]/g, '');
  if (!clean || clean.length === 0) {
    return { valid: false, clean: '', message: 'Vui lòng cung cấp số điện thoại liên hệ.' };
  }
  // Chuẩn số điện thoại Việt Nam: bắt đầu bằng 0, độ dài 10 hoặc 11 chữ số
  const regex = /^0[0-9]{9,10}$/;
  if (!regex.test(clean)) {
    return { valid: false, clean, message: 'Số điện thoại không hợp lệ (bắt đầu bằng 0, gồm 10 hoặc 11 chữ số).' };
  }
  return { valid: true, clean, message: '' };
}

function moValidateEmail(val) {
  const clean = (val || '').trim();
  if (!clean || clean.length === 0) {
    return { valid: false, message: 'Vui lòng cung cấp địa chỉ email.' };
  }
  const regex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
  if (!regex.test(clean)) {
    return { valid: false, message: 'Địa chỉ email không đúng định dạng (Ví dụ: name@company.com).' };
  }
  return { valid: true, message: '' };
}

function moValidateFullName(val, minLen = 2) {
  const clean = (val || '').trim();
  if (!clean || clean.length < minLen) {
    return { valid: false, message: `Họ và tên phải có tối thiểu ${minLen} ký tự.` };
  }
  return { valid: true, message: '' };
}

function moValidateRequired(val, fieldName = 'Thông tin này') {
  const clean = (val || '').trim();
  if (!clean || clean.length === 0) {
    return { valid: false, message: `${fieldName} là bắt buộc.` };
  }
  return { valid: true, message: '' };
}

function moValidateMinMax(val, min = null, max = null, fieldName = 'Giá trị') {
  const num = parseFloat(val);
  if (isNaN(num)) {
    return { valid: false, message: `${fieldName} phải là dạng số hợp lệ.` };
  }
  if (min !== null && num < min) {
    return { valid: false, message: `${fieldName} tối thiểu phải là ${min}.` };
  }
  if (max !== null && num > max) {
    return { valid: false, message: `${fieldName} tối đa không vượt quá ${max}.` };
  }
  return { valid: true, message: '' };
}

function moValidateFutureDate(dateVal, minDays = 0) {
  if (!dateVal) {
    return { valid: false, message: 'Vui lòng chọn thời gian hợp lệ.' };
  }
  const selectedTime = new Date(dateVal).getTime();
  const minTime = Date.now() + (minDays * 24 * 60 * 60 * 1000);
  if (selectedTime < minTime) {
    const dayText = minDays > 0 ? `ít nhất ${minDays} ngày tới` : 'từ thời điểm hiện tại trở đi';
    return { valid: false, message: `Thời gian được chọn phải là ${dayText}.` };
  }
  return { valid: true, message: '' };
}

// 4. Đo độ mạnh mật khẩu & cập nhật UI trực quan (Password Strength Engine)
function moEvaluatePassword(val) {
  const raw = val || '';
  const hasLen = raw.length >= 8;
  const hasCase = /[a-z]/.test(raw) && /[A-Z]/.test(raw);
  const hasNum = /[0-9]/.test(raw);
  const hasSym = /[^A-Za-z0-9]/.test(raw);

  let score = 0;
  if (hasLen) score++;
  if (hasCase) score++;
  if (hasNum) score++;
  if (hasSym) score++;

  if (raw.length === 0) {
    return { valid: false, score: 0, hasLen: false, hasCase: false, hasNum: false, hasSym: false, label: 'Chưa nhập', color: '#94a3b8' };
  }

  let color = '#ef4444';
  let label = 'Rất yếu';
  if (score === 1) { color = '#ef4444'; label = 'Yếu'; }
  else if (score === 2) { color = '#f97316'; label = 'Trung bình'; }
  else if (score === 3) { color = '#eab308'; label = 'Khá'; }
  else if (score === 4) { color = '#10b981'; label = 'Rất mạnh (Đạt chuẩn)'; }

  return { valid: score === 4, score, hasLen, hasCase, hasNum, hasSym, label, color };
}

function moUpdateRuleCheck(ruleId, isPassed) {
  const el = document.getElementById(ruleId);
  if (!el) return;
  const icon = el.querySelector('.rule-icon');
  if (isPassed) {
    el.style.color = '#10b981';
    el.style.fontWeight = '600';
    if (icon) icon.className = 'fa-solid fa-circle-check rule-icon';
  } else {
    el.style.color = '#94a3b8';
    el.style.fontWeight = 'normal';
    if (icon) icon.className = 'fa-solid fa-circle-xmark rule-icon';
  }
}

function moRenderPasswordStrength(val, ids = {}) {
  const cfg = Object.assign({
    lenId: 'rule-len',
    caseId: 'rule-case',
    numId: 'rule-num',
    symId: 'rule-sym',
    labelId: 'pwd-strength-label',
    bars: ['pwd-bar-1', 'pwd-bar-2', 'pwd-bar-3', 'pwd-bar-4']
  }, ids);

  const res = moEvaluatePassword(val);

  moUpdateRuleCheck(cfg.lenId, res.hasLen);
  moUpdateRuleCheck(cfg.caseId, res.hasCase);
  moUpdateRuleCheck(cfg.numId, res.hasNum);
  moUpdateRuleCheck(cfg.symId, res.hasSym);

  const labelEl = document.getElementById(cfg.labelId);
  const gray = '#e2e8f0';

  if (labelEl) {
    labelEl.innerText = res.label;
    labelEl.style.color = res.color;
  }

  cfg.bars.forEach((barId, index) => {
    const b = document.getElementById(barId);
    if (b) {
      b.style.background = (res.score >= index + 1 && res.score > 0) ? res.color : gray;
    }
  });

  return res;
}

// 5. Tiện ích Ẩn / Hiện Mật Khẩu (Show/Hide Password)
function moTogglePasswordVisibility(fieldId, btnEl) {
  const input = document.getElementById(fieldId);
  if (!input) return;
  const isPwd = input.type === 'password';
  input.type = isPwd ? 'text' : 'password';
  const icon = btnEl ? btnEl.querySelector('i') : null;
  if (icon) {
    icon.className = isPwd ? 'fa-regular fa-eye-slash' : 'fa-regular fa-eye';
  }
}

// 6. Ánh xạ lỗi tự động từ Laravel 422 Response (Server Error Mapper)
function moMapServerErrors(errors, fieldMapping = {}) {
  if (!errors || typeof errors !== 'object') return;
  let firstFocused = false;

  Object.keys(errors).forEach(key => {
    const msg = Array.isArray(errors[key]) ? errors[key][0] : errors[key];
    const mapping = fieldMapping[key];
    if (mapping && mapping.inputId && mapping.slotId) {
      moSetFieldError(mapping.inputId, mapping.slotId, msg);
      if (!firstFocused) {
        const el = document.getElementById(mapping.inputId);
        if (el) { el.focus(); firstFocused = true; }
      }
    }
  });
}

// 7. Dropdown Tìm Kiếm Tiếng Việt Không Dấu Dùng Chung (Searchable Select Component)
if (typeof window.moSearchableSelect !== 'function') {
  window.moSearchableSelect = function(config) {
    return {
      open: false,
      search: '',
      highlightedIndex: -1,
      items: config.items || [],
      selectedValue: config.initialValue ?? '',
      defaultLabel: config.defaultLabel || '-- Chọn mục --',
      wireProp: config.wireProp || null,

      removeAccents(str) {
        if (!str) return '';
        return str.normalize('NFD')
                  .replace(/[\u0300-\u036f]/g, '')
                  .replace(/đ/g, 'd').replace(/Đ/g, 'D')
                  .toLowerCase();
      },

      get filteredItems() {
        if (!this.search || this.search.trim() === '') {
          return this.items;
        }
        const q = this.removeAccents(this.search.trim());
        return this.items.filter(item => {
          if (!item.value) return false;
          return this.removeAccents(item.label).includes(q);
        });
      },

      get currentLabel() {
        if (!this.selectedValue) return this.defaultLabel;
        const found = this.items.find(i => String(i.value) === String(this.selectedValue));
        return found ? found.label : this.defaultLabel;
      },

      toggle() {
        this.open = !this.open;
        if (this.open) {
          this.search = '';
          this.highlightedIndex = -1;
          this.$nextTick(() => {
            if (this.$refs.searchInput) this.$refs.searchInput.focus();
          });
        }
      },

      close() {
        this.open = false;
        this.search = '';
        this.highlightedIndex = -1;
      },

      select(val) {
        this.selectedValue = val;
        this.close();
        if (this.wireProp && typeof this.$wire !== 'undefined') {
          this.$wire.set(this.wireProp, val === '' ? null : val);
        }
        if (typeof config.onChange === 'function') {
          config.onChange(val);
        }
      },

      onKeyDown(e) {
        if (!this.open) {
          if (e.key === 'Enter' || e.key === ' ' || e.key === 'ArrowDown') {
            e.preventDefault();
            this.toggle();
          }
          return;
        }
        const list = this.filteredItems;
        if (e.key === 'Escape') {
          e.preventDefault();
          this.close();
        } else if (e.key === 'ArrowDown') {
          e.preventDefault();
          if (list.length > 0) {
            this.highlightedIndex = (this.highlightedIndex + 1) % list.length;
          }
        } else if (e.key === 'ArrowUp') {
          e.preventDefault();
          if (list.length > 0) {
            this.highlightedIndex = (this.highlightedIndex - 1 + list.length) % list.length;
          }
        } else if (e.key === 'Enter') {
          e.preventDefault();
          if (this.highlightedIndex >= 0 && this.highlightedIndex < list.length) {
            this.select(list[this.highlightedIndex].value);
          }
        }
      }
    };
  };
}

// 8. Hệ thống Toast Notification góc màn hình chuẩn BMS UX
function moToast(message, type = 'info', title = '') {
  let container = document.getElementById('mo-toast-container');
  if (!container) {
    container = document.createElement('div');
    container.id = 'mo-toast-container';
    container.style.cssText = 'position:fixed;top:20px;right:20px;z-index:999999;display:flex;flex-direction:column;gap:10px;max-width:420px;width:calc(100vw - 40px);pointer-events:none;';
    document.body.appendChild(container);
  }

  const toast = document.createElement('div');
  toast.style.cssText = 'pointer-events:auto;display:flex;align-items:flex-start;gap:12px;padding:14px 16px;background:#ffffff;border-radius:12px;box-shadow:0 10px 30px -5px rgba(15,23,42,0.18), 0 0 0 1px rgba(15,23,42,0.08);transform:translateX(110%);transition:transform 0.28s cubic-bezier(0.16,1,0.3,1), opacity 0.25s ease;opacity:0;';

  let iconHtml = '<i class="fa-solid fa-circle-info" style="color:#2563eb;font-size:18px;margin-top:2px;"></i>';
  let defaultTitle = 'Thông báo';
  let borderLeft = '4px solid #2563eb';

  if (type === 'success') {
    iconHtml = '<i class="fa-solid fa-circle-check" style="color:#10b981;font-size:18px;margin-top:2px;"></i>';
    defaultTitle = 'Thành công';
    borderLeft = '4px solid #10b981';
  } else if (type === 'error' || type === 'danger') {
    iconHtml = '<i class="fa-solid fa-circle-xmark" style="color:#ef4444;font-size:18px;margin-top:2px;"></i>';
    defaultTitle = 'Có lỗi xảy ra';
    borderLeft = '4px solid #ef4444';
  } else if (type === 'warning') {
    iconHtml = '<i class="fa-solid fa-triangle-exclamation" style="color:#f59e0b;font-size:18px;margin-top:2px;"></i>';
    defaultTitle = 'Cảnh báo';
    borderLeft = '4px solid #f59e0b';
  }

  toast.style.borderLeft = borderLeft;
  toast.innerHTML = `
    ${iconHtml}
    <div style="flex:1;min-width:0;">
      <div style="font-weight:700;font-size:13.5px;color:#0f172a;margin-bottom:2px;">${title || defaultTitle}</div>
      <div style="font-size:12.5px;color:#475569;line-height:1.45;word-break:break-word;">${message}</div>
    </div>
    <button type="button" style="background:none;border:none;color:#94a3b8;cursor:pointer;padding:2px;font-size:16px;line-height:1;margin-left:4px;" onclick="this.parentElement.remove()">&times;</button>
  `;

  container.appendChild(toast);
  requestAnimationFrame(() => {
    toast.style.transform = 'translateX(0)';
    toast.style.opacity = '1';
  });

  const timer = setTimeout(() => {
    toast.style.transform = 'translateX(110%)';
    toast.style.opacity = '0';
    setTimeout(() => { if (toast.parentNode) toast.remove(); }, 300);
  }, 4500);

  toast.addEventListener('mouseenter', () => clearTimeout(timer));
}

// Global Aliases đảm bảo truy cập mọi nơi trong hệ thống
window.moSetFieldError = moSetFieldError;
window.moClearFieldError = moClearFieldError;
window.moToast = moToast;
window.moSanitizeNumeric = moSanitizeNumeric;
window.moSanitizeDecimal = moSanitizeDecimal;
window.moFormatNumber = moFormatNumber;
window.moValidatePhone = moValidatePhone;
window.moValidateEmail = moValidateEmail;
window.moValidateFullName = moValidateFullName;
window.moValidateRequired = moValidateRequired;
window.moValidateMinMax = moValidateMinMax;
window.moValidateFutureDate = moValidateFutureDate;
window.moEvaluatePassword = moEvaluatePassword;
window.moUpdateRuleCheck = moUpdateRuleCheck;
window.moRenderPasswordStrength = moRenderPasswordStrength;
window.moTogglePasswordVisibility = moTogglePasswordVisibility;
window.moMapServerErrors = moMapServerErrors;

// Lắng nghe sự kiện mo-toast từ Livewire và Client
window.addEventListener('mo-toast', (e) => {
  const d = Array.isArray(e.detail) ? e.detail[0] : (e.detail || {});
  moToast(d.message || '', d.type || 'info', d.title || '');
});
</script>
