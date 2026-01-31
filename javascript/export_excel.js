function exportTableToExcel(tableId, filename) {
    const table = document.getElementById(tableId);

    const clonedTable = table.cloneNode(true);
    const firstHeaderCell = clonedTable.querySelector("thead tr th");
    if (firstHeaderCell) {
        firstHeaderCell.innerText = "";
    }
    const cells = clonedTable.querySelectorAll("td, th");
    cells.forEach(cell => {
        // Se ci sono input, select o textarea, sostituisci il valore nella cella
        const input = cell.querySelector("input");
        const select = cell.querySelector("select");
        const textarea = cell.querySelector("textarea");

        if (input) {
            cell.innerText = input.value; // Sostituisci con il valore dell'input
        } else if (select) {
            cell.innerText = select.value; // Sostituisci con il valore selezionato
        } else if (textarea) {
            cell.innerText = textarea.value; // Sostituisci con il contenuto del textarea
        }
    });
    const workbook = XLSX.utils.table_to_book(clonedTable);

    // Scrivi il foglio di lavoro in un file Excel e scaricalo
    XLSX.writeFile(workbook, filename || "tabella.xlsx");
}

// Esempio di utilizzo
document.getElementById("download-link-risultati").addEventListener("click", function(event) {
    event.preventDefault();
    exportTableToExcel("result_table_evaluator", "tabella_risultati.xlsx");
});

document.getElementById("download-link-classe").addEventListener("click", function(event) {
    event.preventDefault();
    exportTableToExcel("class_stats_table", "tabella_prima_valutazione.xlsx");
});

document.getElementById("download-link-global").addEventListener("click", function(event)  {
    event.preventDefault();
    exportTableToExcel("global_stats_table", "tabella_seconda_valutazione.xlsx");
});