import { GlobalDialogManager } from "../formWebScripts/js/formDialogScript.js";
import { SendToast } from "../formWebScripts/js/formScript.js";
import { SendPOSTDataToServerAsync } from "../formWebScripts/js/serverComunication.js";
//Format time
for (const element of document.getElementsByClassName("timeFormat")) {
    element.innerHTML = new Date(element.innerHTML.replace(' ', 'T') + 'Z').toLocaleString(navigator.languages[0], {
        year: 'numeric', month: '2-digit', day: '2-digit',
        hour: '2-digit', minute: '2-digit', second: '2-digit',
        hour12: false
    }).replace(',', ' ');
}
//Archive button
for (const button of document.getElementsByClassName("archiveSheetBtn")) {
    button.addEventListener("click", async () => {
        //Confirm
        if (!await GlobalDialogManager.ShowConfirmAsync("Archive", "Are you sure you want to archive this sheet? This cant be undone!")) {
            SendToast("Archive", "Action cancelled.", "info");
            return;
        }
        //Send POST
        const wait = GlobalDialogManager.ShowProgress("Archive", "Please wait, sheet is being archived...", () => { }, 0, false);
        const formData = new FormData();
        formData.set("action", "archive");
        formData.set("sheet", decodeURIComponent(button.getAttribute("sheet")));
        const [ok, resp] = await SendPOSTDataToServerAsync("../user/manageSheet.php", formData);
        if (ok) {
            SendToast("Archive", "Sheet archived!", "ok");
            setTimeout(() => {
                window.location.reload();
            }, 1000);
            return;
        }
        //Error
        wait === null || wait === void 0 ? void 0 : wait.CloseDialog();
        GlobalDialogManager.ShowAlertAsync("Archive", "Error archiving sheet: " + resp);
    });
}
//Archive button
for (const button of document.getElementsByClassName("deleteSheetBtn")) {
    button.addEventListener("click", async () => {
        //Confirm
        if (!await GlobalDialogManager.ShowConfirmAsync("Delete", "Are you sure you want to delete this sheet? This cant be undone!")) {
            SendToast("Delete", "Action cancelled.", "info");
            return;
        }
        //Send POST
        const wait = GlobalDialogManager.ShowProgress("Delete", "Please wait, sheet is being archived...", () => { }, 0, false);
        const formData = new FormData();
        formData.set("action", "delete");
        formData.set("sheet", decodeURIComponent(button.getAttribute("sheet")));
        const [ok, resp] = await SendPOSTDataToServerAsync("./admin.php", formData);
        if (ok) {
            SendToast("Delete", "Sheet deleted!", "ok");
            setTimeout(() => {
                window.location.reload();
            }, 1000);
            return;
        }
        //Error
        wait === null || wait === void 0 ? void 0 : wait.CloseDialog();
        GlobalDialogManager.ShowAlertAsync("Delete", "Error deleting sheet: " + resp);
    });
}
//# sourceMappingURL=admin.js.map