/* jshint esversion: 6 */

document.addEventListener("DOMContentLoaded", function () {
    const checkboxes = document.querySelectorAll('input[type="checkbox"]');

    function cancellaDeselezionato() {
        let i = 1;
        checkboxes.forEach(checkbox =>{
            if (checkbox.name.match(/\[(\d+)]/) !== null) {
                let label = document.querySelector('label[for="' + checkbox.name.match(/\[(\d+)]/)[1] + '"]');
                if (checkbox.checked) {
                    label.textContent = i;
                    i = i + 1;
                } else {
                    label.textContent = '';
                }
            }
        });
    }


    checkboxes.forEach(checkbox => {
        checkbox.addEventListener("change", cancellaDeselezionato);
    });
});
