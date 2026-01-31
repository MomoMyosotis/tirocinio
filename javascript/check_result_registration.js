/* jshint esversion: 6 */

document.addEventListener("DOMContentLoaded", function () {
    const results_cells = document.querySelectorAll('input[type="number"]');
    const method_cells = document.querySelectorAll('select[name^="metodo_didattico"]');
    const prove_accessorie = document.querySelectorAll('select[name^="proveaccessorie"]');
    const inserimenti_parziali = document.querySelectorAll('select[name^="inserimento_parziale"]');
    const selects_mod_scrittura = document.querySelectorAll('select[name^="lettura_modalita"]');

    prove_accessorie.forEach(prova_accessoria => {
        let id_student = prova_accessoria.name.match(/\[(\d+)]/)[1];
        results_cells.forEach(cell => {

            if (cell.name.match(/\[(\d+)]/)[1] === id_student) {
                if (cell.name.startsWith('metafonologia')) {
                    cell.readOnly = prova_accessoria.value === 'No';
                } else {
                    cell.readOnly = prova_accessoria.value === 'Sì';
                }
            }
        });

        selects_mod_scrittura.forEach(select_scrittura => {

            if (select_scrittura.name.match(/\[(\d+)]/)[1] === id_student) {
                select_scrittura.readOnly = event.target.value === 'Sì';
            }
        });

        inserimenti_parziali.forEach(inserimento_parziale => {
            if (inserimento_parziale.name.match(/\[(\d+)]/)[1] === id_student) {
                inserimento_parziale.disabled = prova_accessoria.value === 'Sì';
            }
        });

    });

    inserimenti_parziali.forEach(inserimento_parziale => {
        let id_student = inserimento_parziale.name.match(/\[(\d+)]/)[1];
        prove_accessorie.forEach(prova_accessoria => {
            if (prova_accessoria.name.match(/\[(\d+)]/)[1] === id_student) {
                prova_accessoria.disabled = inserimento_parziale.value === 'Sì';
            }
        });

    });

    prove_accessorie.forEach(prova_accessoria => {
        prova_accessoria.addEventListener("change", function (event) {
            let id_student = event.target.name.match(/\[(\d+)]/)[1];
            results_cells.forEach(cell => {

                if (cell.name.match(/\[(\d+)]/)[1] === id_student) {
                    if (cell.name.startsWith('metafonologia')) {
                        cell.readOnly = event.target.value === 'No';
                    } else {
                        cell.readOnly = event.target.value === 'Sì';
                    }
                }

                if (cell.readOnly) {
                    cell.style.border = '';
                }
            });

            selects_mod_scrittura.forEach(select_scrittura => {

                if (select_scrittura.name.match(/\[(\d+)]/)[1] === id_student) {
                    select_scrittura.readOnly = event.target.value === 'Sì';
                    }
            });

            inserimenti_parziali.forEach(inserimento_parziale => {
                if (inserimento_parziale.name.match(/\[(\d+)]/)[1] === id_student) {
                    inserimento_parziale.disabled = prova_accessoria.value === 'Sì';
                }
            });

        });
    });

    inserimenti_parziali.forEach(inserimento_parziale => {
        inserimento_parziale.addEventListener("change", function (event) {
            let id_student = event.target.name.match(/\[(\d+)]/)[1];
            prove_accessorie.forEach(prova_accessoria => {
                if (prova_accessoria.name.match(/\[(\d+)]/)[1] === id_student) {
                    prova_accessoria.disabled = inserimento_parziale.value === 'Sì';
                }
            });

        });
    });

    results_cells.forEach(cell => {
        cell.addEventListener("change", function () {
           if (cell.value === '' && !cell.readOnly) {
               cell.style.border = '2px solid grey';
           } else {
               cell.style.border = '';
           }
        });
        if (cell.value === '' && !cell.readOnly) {
            cell.style.border = '2px solid grey';
        } else {
            cell.style.border = '';
        }
    });

    method_cells.forEach(cell => {
        cell.addEventListener("change", function () {
            if (cell.value === '' && !cell.readOnly) {
                cell.style.border = '2px solid grey';
            } else {
                cell.style.border = '';
            }
        });
        if (cell.value === '' && !cell.readOnly) {
            cell.style.border = '2px solid grey';
        } else {
            cell.style.border = '';
        }
    });

});
