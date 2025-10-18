document.addEventListener('DOMContentLoaded', () => {

    const nameInput = document.getElementById('name');
    const emailInput = document.getElementById('email');
    
    const nameAlert = document.getElementById('nameAlert');
    const emailAlert = document.getElementById('emailAlert');
    const btnEnviar = document.getElementById('btn-form');

    if (nameInput && nameAlert && btnEnviar) {
        nameInput.onchange = () => {
            if (nameInput.value.trim().length < 1) {
                nameAlert.style.display = "block";
                nameAlert.innerHTML = 'El nombre es obligatorio.';
                nameInput.style.border = "3px solid red";
                btnEnviar.disabled = true;
            } else {
                nameAlert.style.display = "none";
                nameAlert.innerHTML = '';
                nameInput.style.border = "3px solid green";
                btnEnviar.disabled = false;
            }
        }
    }

    if (emailInput && emailAlert && btnEnviar) {
        emailInput.onchange = () => {
            if (emailInput.value.trim().length < 1) {
                emailAlert.style.display = "block";
                emailAlert.innerHTML = 'El correo es obligatorio.';
                emailInput.style.border = "3px solid red";
                btnEnviar.disabled = true;
            } else {
                emailAlert.style.display = "none";
                emailAlert.innerHTML = '';
                emailInput.style.border = "3px solid green";
                btnEnviar.disabled = false;
            }
        }
    }
});
