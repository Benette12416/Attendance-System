/**
 * Generic live-scan handler.
 * The RFID reader behaves like a keyboard: it "types" the UID into
 * whatever text input is focused, then presses Enter.
 * This script listens for Enter on #scanInput, sends the UID to the
 * given endpoint, shows the result, then clears + refocuses the input
 * so the station is ready for the next tap.
 */
function initScanInput(inputId, resultId, endpoint, extraPayload, onSuccess) {
    const input = document.getElementById(inputId);
    const resultBox = document.getElementById(resultId);
    if (!input) return;

    input.focus();

    input.addEventListener('keypress', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            const uid = input.value.trim();
            input.value = '';
            if (!uid) return;

            resultBox.textContent = 'Reading card...';
            resultBox.className = 'scan-result text-muted';

            const payload = Object.assign({ rfid_uid: uid }, extraPayload || {});

            const formData = new FormData();
                formData.append("uid", uid);

                fetch(endpoint, {
                    method: "POST",
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    resultBox.textContent = data.message;
                    resultBox.className = 'scan-result ' + (data.success ? 'text-success' : 'text-danger');
                    resultBox.style.color = data.success ? '#16a34a' : '#dc2626';
                    if (typeof onSuccess === 'function') onSuccess(data);
                })
                .catch(() => {
                    resultBox.textContent = 'Error contacting server.';
                    resultBox.style.color = '#dc2626';
                })
                .finally(() => input.focus());
        }
    });

    // baguhin nalang tong to para sa pag focus ng input kapag nag click sa ibang parte ng page
    document.addEventListener('click', function(e) {

    // Don't steal focus when using form controls
    if (
        e.target.tagName === 'INPUT' ||
        e.target.tagName === 'SELECT' ||
        e.target.tagName === 'TEXTAREA' ||
        e.target.tagName === 'BUTTON'
    ) {
        return;
    }

    input.focus();
});
}
