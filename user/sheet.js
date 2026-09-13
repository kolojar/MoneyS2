import { GlobalDialogManager } from "../formWebScripts/js/formDialogScript.js";
import { SendToast } from "../formWebScripts/js/formScript.js";
import { SendPOSTDataToServerAsync } from "../formWebScripts/js/serverComunication.js";
//Hash
const hash = window.location.hash;
console.log(hash);
window.location.hash = "";
window.location.hash = hash;
//Format time
const params = new URLSearchParams(window.location.search);
for (const element of document.getElementsByClassName("timeFormat")) {
    element.innerHTML = new Date(element.innerHTML.replace(" ", "T") + "Z")
        .toLocaleString(navigator.languages[0], {
        year: "numeric",
        month: "2-digit",
        day: "2-digit",
        hour: "2-digit",
        minute: "2-digit",
        hour12: false,
    })
        .replace(",", " ");
}
//Format time
for (const element of document.getElementsByClassName("dateFormat")) {
    element.innerHTML = new Date(element.innerHTML)
        .toLocaleString(navigator.languages[0], {
        year: "numeric",
        month: "2-digit",
        day: "2-digit",
        hour12: false,
    })
        .replace(",", " ");
}
//Delete buttons
for (const button of document.getElementsByClassName("btnDelete")) {
    button.addEventListener("click", async () => {
        //Confirm
        if (!(await GlobalDialogManager.ShowConfirmAsync("Delete entry", "Are you sure you want to delete entry?"))) {
            SendToast("Delete entry", "Action cancelled!", "info");
            return;
        }
        //Create data
        const wait = GlobalDialogManager.ShowProgress("Delete entry", "Sending data to server, please wait...", () => { }, 0, false);
        const formData = new FormData();
        formData.set("action", "delete");
        formData.set("id", params.get("id"));
        formData.set("item", button.getAttribute("fid"));
        //Send POST
        const [ok, resp] = await SendPOSTDataToServerAsync("./itemInfo.php", formData);
        if (ok) {
            SendToast("Delete entry", "Entry deleted!", "ok");
            setTimeout(() => {
                window.location.reload();
                return;
            }, 1000);
            return;
        }
        wait === null || wait === void 0 ? void 0 : wait.CloseDialog();
        await GlobalDialogManager.ShowAlertAsync("Delete entry", "Failed to delete entry: " + resp);
    });
}
//Prepare names for checkboxes
const namesCheckboxes = new Map();
const names = new Map();
for (const option of document.getElementById("namesEscaped").options) {
    namesCheckboxes.set(decodeURIComponent(option.getAttribute("label")), { value: parseInt(option.getAttribute("value")), checked: true });
    names.set(parseInt(option.getAttribute("value")), decodeURIComponent(option.getAttribute("label")));
}
//Split money button
for (const button of document.getElementsByClassName("btnSplitMoney")) {
    button.addEventListener("click", async () => {
        //Select names
        let result = await GlobalDialogManager.ShowCheckboxSelectAsync("Split money", "Select names to slit with:", -1, namesCheckboxes);
        if (result == -1 || result.length == 0) {
            SendToast("Split money", "Action cancelled!", "info");
            return;
        }
        if (typeof (result) == "number") {
            result = [result];
        }
        //Create data
        const wait = GlobalDialogManager.ShowProgress("Split money", "Sending data to server, please wait...", () => { }, 0, false);
        const data = new FormData();
        data.set("action", "splitMoney");
        data.set("id", params.get("id"));
        data.set("item", button.getAttribute("fid"));
        data.set("users", JSON.stringify(result));
        //Send POST
        const [ok, resp] = await SendPOSTDataToServerAsync("./itemInfo.php", data);
        if (ok) {
            SendToast("Split money", "Money splited!", "ok");
            setTimeout(() => {
                window.location.href = window.location.pathname + window.location.search + "#row" + button.getAttribute("fid");
                window.location.reload();
            }, 1000);
            return;
        }
        wait === null || wait === void 0 ? void 0 : wait.CloseDialog();
        await GlobalDialogManager.ShowAlertAsync("Split money", "Failed to split money: " + resp);
    });
}
//Set amount
for (const cell of document.getElementsByClassName("fieldCount")) {
    cell.addEventListener("click", async () => {
        //Get new value
        const amount = await GlobalDialogManager.ShowPromptAsync("Set used count", "Enter used count:", -1, "number", {
            min: "0",
            max: cell.getAttribute("max"),
            step: "0.001",
            placeholder: cell.innerHTML,
            presetValue: cell.innerHTML
        });
        if (amount == null || amount < 0) {
            SendToast("Set used count", "Action cancelled!", 'info');
            return;
        }
        //Create data
        const wait = GlobalDialogManager.ShowProgress("Set used count", "Sending data to server, please wait...", () => { }, 0, false);
        const data = new FormData();
        data.set("action", "setCount");
        data.set("id", params.get("id"));
        data.set("item", cell.getAttribute("fid"));
        data.set("user", cell.getAttribute("name"));
        data.set("amount", amount.toString());
        //Send POST
        const [ok, resp] = await SendPOSTDataToServerAsync("./itemInfo.php", data);
        if (ok) {
            SendToast("Set used count", "Used count saved!", "ok");
            setTimeout(() => {
                window.location.reload();
            }, 1000);
            return;
        }
        wait === null || wait === void 0 ? void 0 : wait.CloseDialog();
        await GlobalDialogManager.ShowAlertAsync("Set used count", "Failed to set used count: " + resp);
    });
}
//Pay cells
for (const cell of document.getElementsByClassName("cellPay")) {
    cell.addEventListener("click", async () => {
        //Show pay info
        const amount = await GlobalDialogManager.ShowPromptAsync("Pay", "User: " + names.get(parseInt(cell.getAttribute("who-used"))) + " has to pay: " + cell.innerHTML + " to: " + names.get(parseInt(cell.getAttribute("who-paid"))), -1, "number", {
            step: "0.001",
            placeholder: cell.innerHTML,
            presetValue: cell.innerHTML
        });
        if (amount == null || amount < 0) {
            SendToast("Pay", "Action cancelled!", "info");
            return;
        }
        //Ask name
        const name = await GlobalDialogManager.ShowPromptAsync("Pay", "Enter display name", null, "text", { presetValue: "Money transfer", placeholder: "Money transfer", useMinMaxAsLen: true, min: "1" });
        if (name == null) {
            SendToast("Pay", "Action cancelled!", "info");
            return;
        }
        //Create data
        const wait = GlobalDialogManager.ShowProgress("Pay", "Sending data to server, please wait...", () => { }, 0, false);
        const data = new FormData();
        data.set("action", "pay");
        data.set("id", params.get("id"));
        data.set("from", cell.getAttribute("who-used"));
        data.set("to", cell.getAttribute("who-paid"));
        data.set("amount", amount.toString());
        data.set("name", name);
        //Send POST
        const [ok, resp] = await SendPOSTDataToServerAsync("./itemInfo.php", data);
        if (ok) {
            SendToast("Pay", "Payment saved!", "ok");
            setTimeout(() => {
                window.location.href = window.location.pathname + window.location.search + "#row" + cell.getAttribute("target") + cell.getAttribute("who-paid");
                ;
                window.location.reload();
            }, 1000);
            return;
        }
        wait === null || wait === void 0 ? void 0 : wait.CloseDialog();
        await GlobalDialogManager.ShowAlertAsync("Pay", "Failed to save payment: " + resp);
    });
}
//# sourceMappingURL=sheet.js.map