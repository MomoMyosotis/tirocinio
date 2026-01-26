document.addEventListener("DOMContentLoaded", function () {

    document.querySelectorAll("select[name^='valutazione_globale']").forEach(select => {
        select.addEventListener("change", function () {

            let match = this.name.match(/valutazione_globale\[(\d+)\]/);
            if (match) {
                let num = match[1];
                let reportedCell = document.getElementById('reported['.concat(num, "]"));
                if (reportedCell) {
                    if (this.value === "") {
                        reportedCell.textContent = "";
                    } else if (this.value === "Verde chiaro") {
                        reportedCell.textContent = "Non segnalato";
                    } else {
                        reportedCell.textContent = "Segnalato";
                    }
                }
            }
        });
    });
});
