<script>
/**
 * Alpine.js mixin for AJAX form submission.
 *
 * Usage in x-data:
 *   x-data="{ ...ajaxForm({ url: '...', method: 'POST' }) }"
 *   or with options:
 *   x-data="{ ...ajaxForm({ url: '...', method: 'PUT', onSuccess(data) { ... }, resetOnSuccess: true, stayOnPage: true }) }"
 *
 * Provides: saving, errors, submit($refs.form)
 */
function ajaxForm(config = {}) {
    return {
        saving: false,
        errors: {},

        async submit(formEl) {
            if (this.saving) return;
            this.saving = true;
            this.errors = {};

            const formData = new FormData(formEl);
            const method = (config.method || 'POST').toUpperCase();

            // For PUT/PATCH/DELETE, use POST with _method spoofing
            if (['PUT', 'PATCH', 'DELETE'].includes(method)) {
                formData.append('_method', method);
            }

            try {
                const response = await fetch(config.url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                const data = await response.json().catch(() => ({}));

                if (!response.ok) {
                    if (response.status === 422 && data.errors) {
                        this.errors = data.errors;
                        showToast('error', data.message || 'Перевірте правильність заповнення форми.');
                    } else if (response.status === 419) {
                        showToast('error', 'Сесія закінчилась. Оновіть сторінку.');
                        setTimeout(() => window.location.reload(), 1500);
                    } else {
                        showToast('error', data.message || 'Помилка збереження.');
                    }
                    this.saving = false;
                    return;
                }

                showToast('success', data.message || 'Збережено!');

                if (config.onSuccess) {
                    config.onSuccess.call(this, data);
                } else if (!config.stayOnPage && data.redirect_url) {
                    setTimeout(() => window.location.href = data.redirect_url, 600);
                    return; // keep saving=true during redirect
                }

                if (config.resetOnSuccess && formEl) {
                    formEl.reset();
                }

                this.saving = false;
            } catch (e) {
                showToast('error', "Помилка з'єднання з сервером.");
                this.saving = false;
            }
        }
    };
}

/**
 * AJAX delete with confirmation dialog.
 *
 * Usage:
 *   <button @click="ajaxDelete(url, 'Ви впевнені?', () => $el.closest('tr').remove())">
 *   or with redirect:
 *   <button @click="ajaxDelete(url, 'Ви впевнені?', null, redirectUrl)">
 */
async function ajaxDelete(url, confirmMsg, onSuccess, redirectUrl) {
    if (!confirm(confirmMsg || 'Ви впевнені?')) return;

    try {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ _method: 'DELETE' })
        });

        const data = await response.json().catch(() => ({}));

        if (!response.ok) {
            showToast('error', data.message || 'Помилка видалення.');
            return;
        }

        showToast('success', data.message || 'Видалено!');

        if (onSuccess) {
            onSuccess(data);
        } else if (redirectUrl || data.redirect_url) {
            setTimeout(() => window.location.href = redirectUrl || data.redirect_url, 600);
        }
    } catch (e) {
        showToast('error', "Помилка з'єднання з сервером.");
    }
}

/**
 * Generic AJAX action (toggle, mark, etc).
 *
 * Usage:
 *   const data = await ajaxAction(url, 'POST');
 *   const data = await ajaxAction(url, 'PUT', { status: 'active' });
 */
async function ajaxAction(url, method = 'POST', body = {}) {
    const actualMethod = method.toUpperCase();
    const sendMethod = ['PUT', 'PATCH', 'DELETE'].includes(actualMethod) ? 'POST' : actualMethod;

    if (['PUT', 'PATCH', 'DELETE'].includes(actualMethod)) {
        body._method = actualMethod;
    }

    const response = await fetch(url, {
        method: sendMethod,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(body)
    });

    const data = await response.json().catch(() => ({}));

    if (!response.ok) {
        if (response.status === 419) {
            showToast('error', 'Сесія закінчилась. Оновіть сторінку.');
            setTimeout(() => window.location.reload(), 1500);
        } else {
            showToast('error', data.message || 'Помилка.');
        }
        throw new Error(data.message || 'Request failed');
    }

    showToast('success', data.message || 'Готово!');
    return data;
}
</script>
