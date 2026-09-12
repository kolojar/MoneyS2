import { GlobalDialogManager } from "../formWebScripts/js/formDialogScript.js";
import { SendToast } from "../formWebScripts/js/formScript.js";
import { SendPOSTDataToServerAsync } from "../formWebScripts/js/serverComunication.js";

//Format time
const params = new URLSearchParams(window.location.search)
for (const element of document.getElementsByClassName("timeFormat")) {
  element.innerHTML = new Date(element.innerHTML.replace(' ', 'T') + 'Z').toLocaleString(navigator.languages[0], {
    year: 'numeric', month: '2-digit', day: '2-digit',
    hour: '2-digit', minute: '2-digit', second: '2-digit',
    hour12: false
  }).replace(',', ' ')
}

//Get names
const names = [];
for (const option of (document.getElementById("namesEscaped") as HTMLDataListElement).options) {
  names.push(decodeURIComponent(option.value))
}

//Delete buttons
for (const button of document.getElementsByClassName("btnDelete")) {
  button.addEventListener("click", async () => {
    //Confirm
    if (! await GlobalDialogManager.ShowConfirmAsync("Delete entry", "Are you sure you want to delete entry?")) {
      SendToast("Delete entry", "Action cancelled!", "info")
      return
    }

    //Create data
    const wait = GlobalDialogManager.ShowProgress("Delete entry", "Sending data to server, please wait...", () => { }, 0, false)
    const formData = new FormData();
    formData.set("action", "delete")
    formData.set("id", params.get("id") as string)
    formData.set("item", button.getAttribute("fid") as string)

    //Send POST
    const [ok, resp] = await SendPOSTDataToServerAsync("./itemInfo.php", formData)
    if (ok) {
      SendToast("Delete entry", "Entry deleted!", "ok")
      setTimeout(() => {
        window.location.reload()
        return
      }, 1000)
      return
    }
    wait?.CloseDialog()
    await GlobalDialogManager.ShowAlertAsync("Delete entry", "Failed to delete entry: " +  resp)
  })
}
