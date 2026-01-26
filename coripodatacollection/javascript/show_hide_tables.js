let table_modified = false;
let global_modified = false;
let classe_modified = false;
const title = document.getElementById('classname');
const title_original = document.getElementById('classname').textContent;
var pageKey = window.location.href;
pageKey = pageKey.replace(/(&close_eval=1|&close_global=1)/g, "");


function check_messagges() {
    const stats_classe = document.getElementById('class_stats_table');
    const close_eval_error_class = document.getElementById('class_evaluation_incomplete');
    if (stats_classe && close_eval_error_class) {
        const stats_classe_form = stats_classe.closest('form');
        close_eval_error_class.style.display = stats_classe_form.style.display;
    }
    const close_eval_allert_class = document.getElementById('class_evaluation_complete');
    if (stats_classe && close_eval_allert_class) {
        const stats_classe_form = stats_classe.closest('form');
        close_eval_allert_class.style.display = stats_classe_form.style.display;
    }

    const stats_global = document.getElementById('global_stats_table');
    const close_eval_error_global = document.getElementById('global_evaluation_incomplete');
    if (stats_global && close_eval_error_global) {
        const stats_global_form = stats_global.closest('form');
        close_eval_error_global.style.display = stats_global_form.style.display;
    }
    const close_eval_allert_global = document.getElementById('global_evaluation_complete');
    if (stats_global && close_eval_allert_global) {
        const stats_global_form = stats_global.closest('form');
        close_eval_allert_global.style.display = stats_global_form.style.display;
    }
}

function check_title() {
    const res_table = document.getElementById('result_table_evaluator');
    const res_form = res_table.closest('form');
    const stats_classe = document.getElementById('class_stats_table');
    const stats_global = document.getElementById('global_stats_table');

    if (res_form.style.display === 'block') {
        title.textContent = title_original + ' - Tabella Risultati';
    }
    if (stats_classe) {
        const stats_classe_form = stats_classe.closest('form');
        if(stats_classe_form.style.display === 'block') {
            title.textContent = title_original + ' - Tabella valutazione primaria';
        }
    }
    if (stats_global) {
        const stats_global_form = stats_global.closest('form');
        if(stats_global_form.style.display === 'block') {
            title.textContent = title_original + ' - Tabella valutazione definitiva';
        }
    }
}


document.getElementById('result_table_evaluator').closest('form').addEventListener('input', function() {
    table_modified = true;
});

if (document.getElementById('class_stats_table')) {
    document.getElementById('class_stats_table').closest('form').addEventListener('input', function () {
        classe_modified = true;
    });
}
if (document.getElementById('global_stats_table')) {
    document.getElementById('global_stats_table').closest('form').addEventListener('input', function () {
        global_modified = true;
    });
}


document.getElementsByName("stats-classe").forEach( button => {
    button.addEventListener("click", function () {

        var conferma = true;
        if (table_modified || global_modified || classe_modified) {
            conferma= confirm('Le modifiche apportate potrebbero non essere salvate. Proseguire?');
        }

        if (conferma) {

            table_modified = false;
            global_modified = false;
            classe_modified = false;

            const res_table = document.getElementById('result_table_evaluator');
            const res_form = res_table.closest('form');
            res_form.style = 'display: none;';
            res_form.reset();

            const stats_global = document.getElementById('global_stats_table');
            if (stats_global) {
                const stats_global_form = stats_global.closest('form');
                stats_global_form.style = 'display: none;';
                stats_global_form.reset();
            }

            const stats_classe = document.getElementById('class_stats_table');
            if (stats_classe) {
                const stats_classe_form = stats_classe.closest('form');
                stats_classe_form.style = 'display: block;';
                localStorage.setItem(pageKey, stats_classe.id);
            }
            check_messagges();
            check_title();
        }
    });
});

document.getElementsByName("stats-globale").forEach( button => {
    button.addEventListener("click", function () {

        var conferma = true;
        if (table_modified || global_modified || classe_modified) {
            conferma= confirm('Le modifiche apportate potrebbero non essere salvate. Proseguire?');
        }

        if (conferma) {

            table_modified = false;
            global_modified = false;
            classe_modified = false;

            const res_table = document.getElementById('result_table_evaluator');
            const res_form = res_table.closest('form');
            res_form.style = 'display: none;';
            res_form.reset();

            const stats_global = document.getElementById('global_stats_table');
            if (stats_global) {
                const stats_global_form = stats_global.closest('form');
                stats_global_form.style = 'display: block;';
                localStorage.setItem(pageKey, stats_global.id);
                stats_global_form.reset();
            }

            const stats_classe = document.getElementById('class_stats_table');
            if (stats_classe) {
                const stats_classe_form = stats_classe.closest('form');
                stats_classe_form.style = 'display: none;';
            }
            check_messagges();
            check_title();
        }
    });
});

document.getElementsByName("res-classe").forEach( button => {
    button.addEventListener("click", function () {

        var conferma = true;
        if (table_modified || global_modified || classe_modified) {
            conferma= confirm('Le modifiche apportate potrebbero non essere salvate. Proseguire?');
        }

        if (conferma) {

            table_modified = false;
            global_modified = false;
            classe_modified = false;

            const res_table = document.getElementById('result_table_evaluator');
            const res_form = res_table.closest('form');
            res_form.style = 'display: block;';
            localStorage.setItem(pageKey, res_table.id);

            const stats_global = document.getElementById('global_stats_table');
            if (stats_global) {
                const stats_global_form = stats_global.closest('form');
                stats_global_form.style = 'display: none;';
                stats_global_form.reset();
            }

            const stats_classe = document.getElementById('class_stats_table');
            if (stats_classe) {
                const stats_classe_form = stats_classe.closest('form');
                stats_classe_form.style = 'display: none;';
                stats_classe_form.reset();
            }
            check_messagges();
            check_title();
        }
    });
});


document.addEventListener("DOMContentLoaded", function () {

    const stats_classe = document.getElementById('class_stats_table');
    const stats_global = document.getElementById('global_stats_table');
    const res_table = document.getElementById('result_table_evaluator');
    const close_eval_error_class = document.getElementById('class_evaluation_incomplete');
    const close_eval_error_global = document.getElementById('global_evaluation_incomplete');


    if (stats_classe) {

        stats_classe.classList.add('fixed-table-stats');
        stats_classe.parentElement.classList.add('fixed-table-stats-wrapper');

        const stats_classe_form = stats_classe.closest('form');
        stats_classe_form.style = 'display: block;';

        const res_form = res_table.closest('form');
        res_form.style = 'display: none;';
    }
    if (stats_global) {

        stats_global.classList.add('fixed-table-stats');
        stats_global.parentElement.classList.add('fixed-table-stats-wrapper');

        const stats_global_form = stats_global.closest('form');
        stats_global_form.style = 'display: none;';
    }

    const lastDisplayed = localStorage.getItem(pageKey);
    if (lastDisplayed) {

        if (lastDisplayed === 'result_table_evaluator') {
            res_table.closest('form').style = 'display: block';
            if (stats_classe) {
                stats_classe.closest('form').style = 'display: none';
            }
            if (stats_global) {
                stats_global.closest('form').style = 'display: none';
            }
        } else if (lastDisplayed === 'class_stats_table') {
            res_table.closest('form').style = 'display: none';
            if (stats_classe) {
                stats_classe.closest('form').style = 'display: block';
            }
            if (stats_global) {
                stats_global.closest('form').style = 'display: none';
            }
        } else if (lastDisplayed === 'global_stats_table') {
            res_table.closest('form').style = 'display: none';
            if (stats_classe) {
                stats_classe.closest('form').style = 'display: none';
            }
            if (stats_global) {
                stats_global.closest('form').style = 'display: block';
            }
        }

        if (!stats_classe && !stats_global) {
            res_table.closest('form').style = 'display: block';
        } else if (!stats_global && lastDisplayed === 'global_stats_table') {
            if (stats_classe) {
                stats_classe.closest('form').style = 'display: block';
                res_table.closest('form').style = 'display: none';
            } else {
                res_table.closest('form').style = 'display: block';
            }
        }

    }




    if (close_eval_error_class) {
        const selects = document.querySelectorAll('select[name^="valutazione_classe"]');
        selects.forEach(select => {
            if (select.closest('table').id === 'class_stats_table') {
                if (select.value === '') {
                    select.style.border = '2px solid red';
                }
                select.addEventListener("change", function () {
                    select.style.border = '';
                });
            }
        });
    }
    if (close_eval_error_global) {
        const selects = document.querySelectorAll('select[name^="valutazione_globale"]');
        selects.forEach(select => {
            if (select.closest('table').id === 'global_stats_table') {
                if (select.value === '') {
                    select.style.border = '2px solid red';
                }
                select.addEventListener("change", function () {
                    select.style.border = '';
                });
            }
        });
    }
    check_messagges();
    check_title();

});