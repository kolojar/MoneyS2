var _a;
import { GlobalDialogManager } from "../formWebScripts/js/formDialogScript.js";
import { SendToast } from "../formWebScripts/js/formScript.js";
import { SendPOSTDataToServerAsync } from "../formWebScripts/js/serverComunication.js";
const params = new URLSearchParams(window.location.search);
//Setup change name button
const changeNameButton = document.getElementById("changeNameButton");
changeNameButton.addEventListener("click", async () => {
    //Get new name
    const name = await GlobalDialogManager.ShowPromptAsync("Enter name", "Enter new password for sheet:", null, "text", { useMinMaxAsLen: true, min: "1", max: "64", placeholder: changeNameButton.getAttribute("value"), presetValue: changeNameButton.getAttribute("value") });
    if (name == null) {
        SendToast("Change name", "Action canceled!", "info");
        return;
    }
    //Send request
    const wait = GlobalDialogManager.ShowProgress("Change name", "Sending data to server, please wait...", () => { }, 0, false);
    const formData = new FormData();
    formData.set("action", "changeName");
    formData.set("name", name);
    formData.set("id", params.get("id"));
    //Validate
    const [ok, resp] = await SendPOSTDataToServerAsync("./manageSheet.php", formData);
    if (ok) {
        SendToast("Change name", "Name changed!", "ok");
        setTimeout(() => {
            window.location.reload();
        }, 1000);
        return;
    }
    wait === null || wait === void 0 ? void 0 : wait.CloseDialog();
    await GlobalDialogManager.ShowAlertAsync("Change name", "Failed to change name: " + resp);
});
//Setup change password button
(_a = document.getElementById("changePasswordButton")) === null || _a === void 0 ? void 0 : _a.addEventListener("click", async () => {
    //Get new name
    const password = await GlobalDialogManager.ShowPromptAsync("Enter password", "Enter new name for sheet:", null, "password");
    if (password == null) {
        SendToast("Change password", "Action canceled!", "info");
        return;
    }
    //Send request
    const wait = GlobalDialogManager.ShowProgress("Change password", "Sending data to server, please wait...", () => { }, 0, false);
    const formData = new FormData();
    formData.set("action", "changePassword");
    formData.set("password", password);
    formData.set("id", params.get("id"));
    //Validate
    const [ok, resp] = await SendPOSTDataToServerAsync("./manageSheet.php", formData);
    if (ok) {
        SendToast("Change password", "Password changed!", "ok");
        setTimeout(() => {
            window.location.reload();
        }, 1000);
        return;
    }
    wait === null || wait === void 0 ? void 0 : wait.CloseDialog();
    await GlobalDialogManager.ShowAlertAsync("Change password", "Failed to change password: " + resp);
});
//Archive button
for (const button of document.getElementsByClassName("archiveButton")) {
    button.addEventListener("click", async () => {
        //Confirm
        if (!(await GlobalDialogManager.ShowConfirmAsync("Archive", "Are you sure you want to archive this sheet? This cant be undone!"))) {
            SendToast("Archive", "Action cancelled.", "info");
            return;
        }
        //Send POST
        const wait = GlobalDialogManager.ShowProgress("Archive", "Please wait, sheet is being archived...", () => { }, 0, false);
        const formData = new FormData();
        formData.set("action", "archive");
        formData.set("sheet", params.get("id"));
        const [ok, resp] = await SendPOSTDataToServerAsync("./manageSheet.php", formData);
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
//# sourceMappingURL=manageSheet.js.map