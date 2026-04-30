import { fetchData } from "./utils/fetch-utils.js";
import { formatDateOrEmpty } from "./utils/date-utils.js";
import { getCmdColor, getCmdColorRmq } from "./utils/color-utils.js";
import {
  clearTableContents,
  displayEmptyMessage,
} from "./utils/table-utils.js";
import { baseUrl } from "../utils/config.js";

export function fetchDetailModal(id, signal, loading, dataContent) {
  const url = `${baseUrl}/api/detail-modal/${id}`;
  fetchData(url, signal)
    .then((data) => {
      if (data.length > 0) {
        const isTypeCis = data[0].numor.startsWith("5");

        data.forEach((detail) => {
          updateOrDetails(detail);
        });

        const columns = defineColumns(isTypeCis);

        const formattedData = data.map((detail) =>
          formatDetailData(detail, isTypeCis)
        );

        clearTableContents("table-container-detail");

        const tableau = new TableauComponent({
          columns: columns,
          data: formattedData,
          theadClass: "table",
        });
        tableau.mount("table-container-detail");

        toggleSpinner(loading, dataContent, false);
      } else {
        displayEmptyMessage("table-container-detail");
        toggleSpinner(loading, dataContent, false);
      }
    })
    .catch((error) => {
      handleFetchError(error);
    });
}

function defineColumns(isTypeCis) {
  return [
    { key: "numor", label: "N° OR", align: "center" },
    { key: "intv", label: "Intv", align: "left" },
    ...(isTypeCis ? [{ key: "numcis", label: "N° CIS", align: "center" }] : []),
    {
      key: "numCde",
      label: "N° Commande",
      styles: (row) => getCmdColor(row),
      align: "center",
    },
    {
      key: "statrmq",
      label: "Statut ctrmrq",
      styles: (row) => getCmdColorRmq(row),
      align: "center",
    },
    { key: "cst", label: "CST", align: "center" },
    { key: "ref", label: "Ref", align: "left" },
    { key: "qteres_or", label: "Qté OR", align: "center" },
    { key: "qteall", label: "Qté ALL", align: "center" },
    { key: "qtereliquat", label: "Qté RLQ", align: "center" },
    { key: "qteliv", label: "Qté LIV", align: "center" },
    { key: "statut", label: "Statut", align: "center" },
    { key: "datestatut", label: "Date Statut", align: "center" },
    { key: "Eta_ivato", label: "ETA Ivato", align: "center" },
    { key: "Eta_magasin", label: "ETA Magasin", align: "center" },
    { key: "message", label: "Message", align: "left" },
  ];
}

function formatDetailData(detail, isTypeCis) {
  return {
    numcis: detail.numcis || "",
    numor: detail.numor,
    intv: detail.intv,
    numCde: detail.numerocmd,
    statrmq: isTypeCis
      ? valueOrEmpty(detail.statut_ctrmq_cis)
      : valueOrEmpty(detail.statut_ctrmq),
    cst: detail.cst,
    ref: detail.ref,
    qteres_or: parseInt(detail.qteres_or),
    qteall: parseInt(detail.qteall),
    qtereliquat: parseInt(detail.qtereliquat),
    qteliv: parseInt(detail.qteliv),
    statut: detail.statut,
    datestatut: formatDateOrEmpty(detail.datestatut),
    Eta_ivato: formatDateOrEmpty(detail.Eta_ivato),
    Eta_magasin: formatDateOrEmpty(detail.Eta_magasin),
    message: detail.message,
    Ord: detail.Ord,
    qteSolde: parseInt(detail.qteSolde),
    qteQte: parseInt(detail.qteQte),
  };
}

function updateOrDetails(detail) {
  const Ornum = document.getElementById("orIntv");
  Ornum.innerHTML = `${detail.numor} - ${detail.intv} | intitulé : ${detail.commentaire} | `;
  if (detail.plan === "PLANIFIE") {
    Ornum.innerHTML += `planifié le : ${formatDateOrEmpty(
      detail.dateplanning
    )}`;
  } else {
    Ornum.innerHTML += `date début : ${formatDateOrEmpty(detail.dateplanning)}`;
  }
}
