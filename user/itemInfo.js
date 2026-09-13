var _m, _o;
import { GlobalDialogManager } from "../formWebScripts/js/formDialogScript.js";
import { SendToast } from "../formWebScripts/js/formScript.js";
import { SendPOSTDataToServerAsync } from "../formWebScripts/js/serverComunication.js";
import { ConvertDateTimeToUTC_SQL } from "../formWebScripts/js/sharedScripts.js";
//Get elements
const params = new URLSearchParams(window.location.search);
const when = document.getElementById("when");
const where = document.getElementById("where");
const who = document.getElementById("who");
const name = document.getElementById("name");
const count = document.getElementById("count");
const price = document.getElementById("price");
when.SetDateTimeLocal(new Date(when.getAttribute("valueTime") + " UTC"));
//Where search
where.addEventListener("search", async () => {
    const timestamp = new Date();
    const data = new FormData(undefined, null);
    data.set("search", "where");
    data.set("id", params.get("id"));
    data.set("value", where.valueRaw);
    const [ok, msg] = await SendPOSTDataToServerAsync("./itemInfo.php", data);
    if (ok) {
        where.setOptions(JSON.parse(msg), timestamp);
    }
});
where.dispatchEvent(new Event("search"));
//Name search
name.addEventListener("search", async () => {
    const timestamp = new Date();
    const data = new FormData(undefined, null);
    data.set("search", "name");
    data.set("id", params.get("id"));
    data.set("value", name.valueRaw);
    const [ok, msg] = await SendPOSTDataToServerAsync("./itemInfo.php", data);
    if (ok) {
        name.setOptions(JSON.parse(msg), timestamp);
    }
});
name.dispatchEvent(new Event("search"));
//Clear button
(_m = document.getElementById("btnClear")) === null || _m === void 0 ? void 0 : _m.addEventListener("click", async () => {
    //Confirm
    if (!(await GlobalDialogManager.ShowConfirmAsync("Clear form", "Are you sure you want to clear this form. Action cant be undone!"))) {
        SendToast("Clear form", "Action cancelled!", "info");
    }
    when.SetDateTimeLocal(new Date());
    where.value = "";
    who.value = "";
    name.value = "";
    count.value = "0";
    price.value = "0.00";
    where.validate();
    who.validate();
    name.validate();
    count.validate();
    price.validate();
});
//Restore from memory button
(_o = document.getElementById("btnRestore")) === null || _o === void 0 ? void 0 : _o.addEventListener("click", async () => {
    //Get last item from storage
    const item = sessionStorage.getItem("addItem");
    if (item == null || item == "") {
        SendToast("Restore from memory", "Memory is empty.", "info");
        return;
    }
    //Parse JSON
    const memory = JSON.parse(item);
    if (memory.id != params.get("id")) {
        sessionStorage.removeItem("addItem");
        SendToast("Restore from memory", "Memory is empty.", "info");
        return;
    }
    //Put to inputs
    when.value = memory.when;
    where.value = memory.where;
    who.value = memory.who;
    name.value = memory.name;
    count.value = memory.count;
    price.value = memory.price;
    when.validate();
    where.validate();
    who.validate();
    name.validate();
    count.validate();
    price.validate();
    SendToast("Restore from memory", "Memory restored", "ok");
});
//Save buttons
for (const button of document.getElementsByClassName("btnSave")) {
    button.addEventListener("click", async () => {
        //Validate
        const wait = GlobalDialogManager.ShowProgress("Save", "Saving data to server, please wait...", () => { }, 0, false);
        const [_a, ok1, _b] = await when.validate();
        const [_c, ok2, _d] = await where.validate();
        const [_e, ok3, _f] = await who.validate();
        const [_g, ok4, _h] = await name.validate();
        const [_i, ok5, _j] = await count.validate();
        const [_k, ok6, _l] = await price.validate();
        if (!ok1 || !ok2 || !ok3 || !ok4 || !ok5 || !ok6) {
            SendToast("Save", "Cant save data because inputs are not valid!", "error");
            wait === null || wait === void 0 ? void 0 : wait.CloseDialog();
            return;
        }
        //Create data
        const formData = new FormData();
        formData.set("action", params.has("item") ? "update" : "insert");
        formData.set("id", params.get("id"));
        formData.set("item", params.has("item") ? params.get("item") : "-1");
        console.log(when.value);
        formData.set("when", ConvertDateTimeToUTC_SQL(new Date(when.value)));
        formData.set("where", where.value);
        formData.set("who", who.value);
        formData.set("name", name.value);
        formData.set("count", count.value);
        formData.set("price", price.value);
        //Create JSON for memory
        const jsonData = {};
        jsonData["id"] = params.get("id");
        jsonData["when"] = when.value;
        jsonData["where"] = where.value;
        jsonData["who"] = who.value;
        jsonData["name"] = name.value;
        jsonData["count"] = count.value;
        jsonData["price"] = price.value;
        sessionStorage.setItem("addItem", JSON.stringify(jsonData));
        //Send POST
        const [ok, resp] = await SendPOSTDataToServerAsync("./itemInfo.php", formData);
        if (ok) {
            SendToast("Save", "Values saved!", "ok");
            if (button.getAttribute("exit") == "1") {
                setTimeout(() => {
                    window.location.href = "./sheet.php?id=" + params.get("id") + (params.has("item") ? "#row" + params.get("item") : "");
                }, 1000);
            }
            else {
                wait === null || wait === void 0 ? void 0 : wait.CloseDialog();
            }
            return;
        }
        wait === null || wait === void 0 ? void 0 : wait.CloseDialog();
        await GlobalDialogManager.ShowAlertAsync("Save", "Error saving data: " + resp);
    });
}
//# sourceMappingURL=itemInfo.js.map