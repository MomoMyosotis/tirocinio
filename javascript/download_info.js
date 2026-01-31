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
            cell.innerText = input.value;
        } else if (select) {
            cell.innerText = select.value;
        } else if (textarea) {
            cell.innerText = textarea.value;
        }
    });
    const workbook = XLSX.utils.table_to_book(clonedTable);
    XLSX.writeFile(workbook, filename || "tabella.xlsx");
}

document.getElementById("get-all-info").addEventListener("click", function(event) {
    event.preventDefault();
    exportTableToExcel("table-infos-erogation", "erogation_infos.xlsx");
});