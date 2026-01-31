/* jshint esversion: 6 */

document.addEventListener("DOMContentLoaded", function () {

    const table = document.getElementById('result_table');
    if (table) {
        table.classList.add('fixed-table');

        const tablediv = table.parentElement;
        tablediv.classList.add('fixed-table-wrapper');
    } else {

        const table = document.getElementById('result_table_evaluator');
        if (table) {
            table.classList.add('fixed-table-thirdcol');

            const tablediv = table.parentElement;
            tablediv.classList.add('fixed-table-thirdcol-wrapper');
        }
    }
});
