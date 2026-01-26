/* jshint esversion: 6 */

document.addEventListener("DOMContentLoaded", function () {
    const selects = document.querySelectorAll("select");
    const checkboxes = document.querySelectorAll('input[type="checkbox"]');

    let receiving_classes = [];
    let merging_classes = [];
    let checked_rows = [];

    function aggiornaSelect() {


        selects.forEach(select => {
            select.disabled = false;
            let select_id = select.name.match(/\[(\d+)]/)[1];
            let options = select.options;
            if (select.name.startsWith("union")) {
                if (receiving_classes.includes(select_id)) {
                    select.value = 0;
                    select.disabled = true;
                }
                options.forEach(option => {
                    option.disabled = merging_classes.includes(option.value) || checked_rows.includes(option.value);
                });
            }
            if (checked_rows.includes(select_id)) {
                if (select.name.startsWith("union")) {
                    select.value = 0;
                }
                select.disabled = true;
            }
        });
    }

    function unioneClasse() {
        receiving_classes = [];
        merging_classes = [];
        selects.forEach(select => {
            if (select.name.startsWith("union")) {
                receiving_classes.push(select.value);
                if (select.value !== '0') {
                    merging_classes.push(select.name.match(/\[(\d+)]/)[1]);
                }
            }
        });
        aggiornaSelect();
    }

    function cancellaDeselezionato() {
        checked_rows = [];
        checkboxes.forEach(checkbox => {
            if (checkbox.name.match(/\[(\d+)]/) !== null) {
                if (!checkbox.checked) {
                    checked_rows.push(checkbox.name.match(/\[(\d+)]/)[1]);
                }
            }
        });

        selects.forEach(select => {
            if (checked_rows.includes(select.value)) {
                select.value = 0;
            }
        });

        unioneClasse();
    }

    // Aggiungi listener a ogni select per rilevare i cambiamenti
    selects.forEach(select => {
        select.addEventListener("change", unioneClasse);
    });

    checkboxes.forEach(checkbox => {
        checkbox.addEventListener("change", cancellaDeselezionato);
    });
});
