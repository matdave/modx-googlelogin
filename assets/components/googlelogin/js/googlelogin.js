const removeLoginOptions = () => {
    const form = document.getElementById('modx-login-form');
    form.querySelectorAll('input').forEach((input) => {
        input.remove();
    });
    form.querySelectorAll('label').forEach((label) => {
        label.remove();
    });
    form.querySelectorAll('button').forEach((button) => {
        button.remove();
    });
}