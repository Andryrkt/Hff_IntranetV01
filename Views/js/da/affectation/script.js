import { getAllReferences } from "../data/fetchData";

document.addEventListener("DOMContentLoaded", async function () {
  const references = await getAllReferences();
  console.log(references);
});
