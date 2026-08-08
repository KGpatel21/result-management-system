// ============================================================
// FILE: main.js
// PURPOSE: Simple JavaScript helper functions
// ============================================================

// ---------- FORM VALIDATION ----------
// Checks if all required fields are filled before submitting
function validateForm(formId) {
    var form = document.getElementById(formId);
    var inputs = form.querySelectorAll('[required]');

    for (var i = 0; i < inputs.length; i++) {
        if (inputs[i].value.trim() === '') {
            alert('Please fill all required fields!');
            inputs[i].focus();
            return false;
        }
    }
    return true;
}

// ---------- CONFIRM DELETE ----------
// Shows confirmation popup before deleting
function confirmDelete(itemName) {
    return confirm('Are you sure you want to delete ' + itemName + '?');
}

// ---------- REFRESH CAPTCHA ----------
// Reloads the CAPTCHA image
function refreshCaptcha() {
    var img = document.getElementById('captcha-img');
    img.src = '../captcha.php?' + Math.random();
}
