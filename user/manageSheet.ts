import { GlobalDialogManager } from "../formWebScripts/js/formDialogScript"
import { SendToast } from "../formWebScripts/js/formScript"

//Setup change name button
document.getElementById("changeNameButton")?.addEventListener("click", async () => {
  //Get new name
  const name = await GlobalDialogManager.ShowPromptAsync("Enter name", "Enter new name for sheet:", null, "text", { useMinMaxAsLen: true, min: "1", max: "64" })
  if (name == null) {
    SendToast("Change name", "Action canceled!", "info")
    return
  }

  //Send request
})
