"use strict";
//Format time
for (const element of document.getElementsByClassName("timeFormat")) {
    element.innerHTML = new Date(element.innerHTML.replace(' ', 'T') + 'Z').toLocaleString(navigator.languages[0], {
        year: 'numeric', month: '2-digit', day: '2-digit',
        hour: '2-digit', minute: '2-digit', second: '2-digit',
        hour12: false
    }).replace(',', ' ');
}
//Get names
const names = [];
for (const option of document.getElementById("namesEscaped").options) {
    names.push(decodeURIComponent(option.value));
}
//# sourceMappingURL=sheet.js.map