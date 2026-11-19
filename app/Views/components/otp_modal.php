<div id="otpModalOverlay" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.55); z-index:9999; align-items:center; justify-content:center;">
    <div style="background:#fff; width:100%; max-width:400px; border-radius:10px; padding:24px; box-shadow:0 8px 24px rgba(0,0,0,0.2); text-align:center; font-family:sans-serif;">
        <h3 style="margin:0 0 8px; color:#1565C0;">Xác thực mã OTP</h3>
        <p id="otpSubText" style="font-size:14px; color:#555; margin:0 0 14px;">Đang gửi mã OTP tới email của bạn...</p>

        <div style="margin-bottom:14px; font-size:14px; font-weight:600;">
            Hiệu lực còn lại: <span id="otpCountdown" style="color:#D32F2F;">01:00</span>
        </div>

        <input type="text" id="otpCodeInput" maxlength="6" placeholder="••••••"
               style="width:75%; padding:10px; font-size:22px; letter-spacing:8px; text-align:center; border:2px solid #ccc; border-radius:8px; margin-bottom:12px;">

        <div id="otpFeedback" style="min-height:20px; font-size:13px; margin-bottom:14px; font-weight:500;"></div>

        <div style="display:flex; gap:10px;">
            <button type="button" id="btnVerifyOtp"
                    style="flex:1; padding:10px; background:#1565C0; color:#fff; border:none; border-radius:6px; font-weight:bold; cursor:pointer;">
                Xác nhận
            </button>
            <button type="button" id="btnResendOtp"
                    style="padding:10px 14px; background:#f1f3f5; color:#333; border:1px solid #ccc; border-radius:6px; cursor:pointer;">
                Gửi lại OTP
            </button>
        </div>

        <button type="button" id="btnCloseOtpModal"
                style="margin-top:12px; background:none; border:none; color:#777; font-size:13px; cursor:pointer; text-decoration:underline;">
            Đóng
        </button>
    </div>
</div>

<script>
const OtpManager = (() => {
    let timer = null;
    let sendUrl = '';
    let verifyUrl = '';
    let payload = {};

    const overlay     = document.getElementById('otpModalOverlay');
    const subText     = document.getElementById('otpSubText');
    const countdownEl = document.getElementById('otpCountdown');
    const inputEl     = document.getElementById('otpCodeInput');
    const feedbackEl  = document.getElementById('otpFeedback');
    const btnVerify   = document.getElementById('btnVerifyOtp');
    const btnResend   = document.getElementById('btnResendOtp');
    const btnClose    = document.getElementById('btnCloseOtpModal');

    function setMsg(text, isError = true) {
        feedbackEl.style.color = isError ? '#D32F2F' : '#2E7D32';
        feedbackEl.textContent = text;
    }

    function startTimer(seconds) {
        clearInterval(timer);
        let left = seconds;
        btnVerify.disabled = false;
        btnVerify.style.opacity = '1';

        const render = () => {
            const m = String(Math.floor(left / 60)).padStart(2, '0');
            const s = String(left % 60).padStart(2, '0');
            countdownEl.textContent = `${m}:${s}`;
        };

        render();
        timer = setInterval(() => {
            left--;
            if (left <= 0) {
                clearInterval(timer);
                countdownEl.textContent = '00:00';
                btnVerify.disabled = true;
                btnVerify.style.opacity = '0.5';
                setMsg('OTP không hợp lệ (đã hết hạn). Vui lòng nhấn gửi lại OTP.', true);
            } else {
                render();
            }
        }, 1000);
    }

    async function requestOtp() {
        btnResend.disabled = true;
        setMsg('Đang gửi mã OTP...', false);
        try {
            const res = await fetch(sendUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const data = await res.json();
            if (data.success) {
                subText.textContent = data.message;
                setMsg('');
                inputEl.value = '';
                inputEl.focus();
                startTimer(data.expires_in || 60);
            } else {
                clearInterval(timer);
                countdownEl.textContent = '--:--';
                btnVerify.disabled = true;
                btnVerify.style.opacity = '0.5';
                setMsg(data.message, true);
            }
        } catch (e) {
            setMsg('Lỗi kết nối máy chủ.', true);
        } finally {
            btnResend.disabled = false;
        }
    }

    function open(config) {
        sendUrl = config.sendUrl;
        verifyUrl = config.verifyUrl;
        payload = config.payload;
        inputEl.value = '';
        overlay.style.display = 'flex';
        requestOtp();
    }

    btnVerify.addEventListener('click', async () => {
        const otp = inputEl.value.trim();
        if (otp.length !== 6) {
            setMsg('Vui lòng nhập đủ 6 số OTP.', true);
            return;
        }
        btnVerify.disabled = true;
        try {
            const res = await fetch(verifyUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ otp })
            });
            const data = await res.json();
            if (data.success) {
                clearInterval(timer);
                setMsg(data.message, false);
                setTimeout(() => {
                    if (data.redirect_url) {
                        window.location.href = data.redirect_url;
                    } else {
                        overlay.style.display = 'none';
                        window.location.reload();
                    }
                }, 1200);
            } else {
                setMsg(data.message, true);
                if (data.locked || data.expired) {
                    clearInterval(timer);
                    btnVerify.disabled = true;
                    btnVerify.style.opacity = '0.5';
                } else {
                    btnVerify.disabled = false;
                    inputEl.value = '';
                    inputEl.focus();
                }
            }
        } catch (e) {
            setMsg('Lỗi kết nối máy chủ.', true);
            btnVerify.disabled = false;
        }
    });

    btnResend.addEventListener('click', requestOtp);
    btnClose.addEventListener('click', () => {
        clearInterval(timer);
        overlay.style.display = 'none';
    });

    return { open };
})();
</script>
