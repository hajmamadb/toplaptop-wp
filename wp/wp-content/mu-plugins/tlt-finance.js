(() => {
    const latin = value => value.replace(/[۰-۹٠-٩]/g, digit => String('۰۱۲۳۴۵۶۷۸۹٠١٢٣٤٥٦٧٨٩'.indexOf(digit) % 10));
    function initialize() {
        document.querySelectorAll('.tlt-budget-search').forEach(form => {
            form.addEventListener('formdata', event => {
                ['product_cat', 'max_price'].forEach(key => {
                    if (!event.formData.get(key)) event.formData.delete(key);
                });
            });
            form.addEventListener('submit', event => {
                const input = form.querySelector('[name="max_price"]');
                const error = form.querySelector('.tlt-budget-error');
                const value = latin(input.value).replace(/[\s,٬]/g, '');
                if (value && (!/^\d+$/.test(value) || !Number.isSafeInteger(Number(value)) || Number(value) <= 0)) {
                    event.preventDefault();
                    error.textContent = 'بودجه مثبت و معتبر به تومان وارد کنید.';
                    input.focus();
                    return;
                }
                input.value = value;
                error.textContent = '';
            });
        });
        const price = document.getElementById('tlt-calc-price');
        const plan = document.getElementById('tlt-calc-plan');
        const result = document.getElementById('tlt-calc-result');
        if (!price || !plan || !result) return;
        const format = new Intl.NumberFormat('fa-IR', { maximumFractionDigits: 0 });
        function calculate() {
            const raw = latin(price.value).replace(/[\s,٬]/g, '');
            const amount = Number(raw);
            const months = Number(plan.value);
            if (!/^\d+$/.test(raw) || !Number.isSafeInteger(amount) || amount <= 0) {
                result.textContent = 'قیمت معتبر و مثبت به تومان وارد کنید؛ اعداد فارسی و لاتین پذیرفته می‌شوند.';
                return;
            }
            const rate = Number(window.tltFinance?.interestRate ?? 5);
            const down = Math.ceil(amount / 3);
            const principal = amount - down;
            const totalInterest = principal * months * rate / 100;
            const total = principal + totalInterest;
            const monthly = Math.floor(total / months);
            const last = total - monthly * (months - 1);
            const lines = [
                ['پیش‌پرداخت یک‌سوم', `${format.format(down)} تومان`],
                ['مبلغ پایه قابل تقسیط', `${format.format(principal)} تومان`],
                [`سود کل (${format.format(rate)}٪ ماهانه × ${format.format(months)} ماه)`, `${format.format(totalInterest)} تومان`],
                ['مبلغ نهایی اقساط (اصل + سود)', `${format.format(total)} تومان`],
                [`${format.format(months - 1)} قسط اول، هر قسط`, `${format.format(monthly)} تومان`],
                ['قسط آخر با تعدیل گرد کردن', `${format.format(last)} تومان`]
            ];
            result.replaceChildren(...lines.map(([label, value]) => {
                const row = document.createElement('p');
                const strong = document.createElement('strong');
                strong.textContent = value;
                row.append(`${label}: `, strong);
                return row;
            }));
            const formPlan = document.getElementById(window.tltFinance?.planField);
            if (formPlan) formPlan.value = `${format.format(months)} قسط`;
        }
        const formProduct = document.getElementById(window.tltFinance?.productField);
        if (formProduct && window.tltFinance.product) formProduct.value = window.tltFinance.product;
        price.addEventListener('input', calculate);
        plan.addEventListener('change', calculate);
        calculate();
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initialize);
    else initialize();
})();
