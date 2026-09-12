import { FormDialogCheckboxSelectData, GlobalDialogManager } from "../formWebScripts/js/formDialogScript.js";
import { SendToast } from "../formWebScripts/js/formScript.js";
import { SendPOSTDataToServerAsync } from "../formWebScripts/js/serverComunication.js";

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
      second: "2-digit",
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
    const wait = GlobalDialogManager.ShowProgress("Delete entry", "Sending data to server, please wait...", () => {}, 0, false);
    const formData = new FormData();
    formData.set("action", "delete");
    formData.set("id", params.get("id") as string);
    formData.set("item", button.getAttribute("fid") as string);

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
    wait?.CloseDialog();
    await GlobalDialogManager.ShowAlertAsync("Delete entry", "Failed to delete entry: " + resp);
  });
}

//Prepare names for checkboxes
const namesCheckboxes = new Map<string, FormDialogCheckboxSelectData<number>>();
for (const option of (document.getElementById("namesEscaped") as HTMLDataListElement).options) {
  namesCheckboxes.set(option.getAttribute("label") as string, {value:parseInt(option.getAttribute("value") as string), checked: true })
}

//Split money button
for (const button of document.getElementsByClassName("btnSplitMoney")) {
  button.addEventListener("click", async () => {
    //Select names
    let result = await GlobalDialogManager.ShowCheckboxSelectAsync<number>("Split money", "Select names to slit with:", 0, namesCheckboxes);
    if (result == 0 || (result as number[]).length == 0) {
      SendToast("Split money", "Action cancelled!", "info");
      return
    }
    if (typeof (result) == "number") {
      result = [result]
    }

    //Create data
    const wait = GlobalDialogManager.ShowProgress("Split money", "Sending data to server, please wait...", () => { }, 0, false);
    const data = new FormData();
    data.set("action", "splitMoney")
    data.set("id", params.get("id") as string)
    data.set("item", button.getAttribute("fid") as string)
    data.set("users", JSON.stringify(result))

    //Send POST
    const [ok, resp] = await SendPOSTDataToServerAsync("./itemInfo.php", data)
    if (ok) {
      SendToast("Split money", "Money splited!", "ok")
      setTimeout(() => {
        window.location.reload()
      }, 1000)
      return
    }
    wait?.CloseDialog()
    await GlobalDialogManager.ShowAlertAsync("Split money", "Failed to split money: " + resp)
  });
}

//Set amount
for (const cell of document.getElementsByClassName("fieldCount")) {
  cell.addEventListener("click", async () => {
    //Get new value
    const amount = await GlobalDialogManager.ShowPromptAsync("Set used count", "Enter used count:", -1, "number", {
      min: "0",
      max: cell.getAttribute("max") as string,
      step: "0.001",
      placeholder: cell.innerHTML,
      presetValue: cell.innerHTML
    })
    if ( amount == null || amount < 0) {
      SendToast("Set used count", "Action cancelled!", 'info')
      return
    }

    //Create data
    const wait = GlobalDialogManager.ShowProgress("Set used count", "Sending data to server, please wait...", () => { }, 0, false)
    const data = new FormData();
    data.set("action", "setCount")
    data.set("id", params.get("id") as string)
    data.set("item", cell.getAttribute("fid") as string)
    data.set("user", cell.getAttribute("name") as string)
    data.set("amount", amount.toString())

    //Send POST
    const [ok, resp] = await SendPOSTDataToServerAsync("./itemInfo.php", data)
    if (ok) {
      SendToast("Set used count", "Used count saved!", "ok")
      setTimeout(() => {
        window.location.reload()
      }, 1000)
      return
    }
    wait?.CloseDialog()
    await GlobalDialogManager.ShowAlertAsync("Set used count", "Failed to set used count: " + resp)
  })
}

//Pay cells
for (const cell of document.getElementsByClassName("cellPay")) {
  cell.addEventListener("click", async () => {
    //Show pay info
    const amount = await GlobalDialogManager.ShowPromptAsync("Pay", "User: " + cell.getAttribute("who-used") + " has to pay: " + cell.innerHTML + " to: " + cell.getAttribute("who-paid"), -1, "number", {
      step: "0.001",
      placeholder: cell.innerHTML,
      presetValue: cell.innerHTML
    })
    if (amount == null || amount < 0) {
      SendToast("Pay", "Action cancelled!", "info")
      return
    }

    //Ask name
    const name = await GlobalDialogManager.ShowPromptAsync("Pay", "Enter display name", null, "text", { presetValue: "Money transfer", placeholder: "Money transfer", useMinMaxAsLen: true, min: "1" })
    if (name == null) {
      SendToast("Pay", "Action cancelled!", "info")
      return
    }

    //Create data
    const wait = GlobalDialogManager.ShowProgress("Pay", "Sending data to server, please wait...", () => { }, 0, false)
    const data = new FormData();
    data.set("action", "pay")
    data.set("id", params.get("id") as string)
    data.set("from",(namesCheckboxes.get(cell.getAttribute("who-used") as string)?.value.toString()) as string)
    data.set("to", (namesCheckboxes.get(cell.getAttribute("who-paid") as string)?.value.toString()) as string)
    data.set("amount", amount.toString())
    data.set("name", name)

    //Send POST
    const [ok, resp] = await SendPOSTDataToServerAsync("./itemInfo.php", data)
    if (ok) {
      SendToast("Pay", "Payment saved!", "ok")
      setTimeout(() => {
        window.location.reload()
      }, 1000)
      return
    }
    wait?.CloseDialog()
    await GlobalDialogManager.ShowAlertAsync("Pay", "Failed to save payment: " + resp)
  })
}
