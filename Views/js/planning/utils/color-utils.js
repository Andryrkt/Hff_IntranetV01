export function getCmdColor(detail) {
  if (detail.statut === "DISPO STOCK") {
    return { backgroundColor: "#c8ad7f", color: "white" };
  }
  if (["Error", "Back Order"].includes(detail.statut)) {
    return { backgroundColor: "red", color: "white" };
  }
  if (detail.Ord === "ORD") {
    return { backgroundColor: "#9ACD32", color: "white" };
  }
  return {};
}

export function getCmdColorRmq(detail) {
  return detail.qteSolde > 0 && detail.qteSolde !== detail.qteQte
    ? { backgroundColor: "yellow" }
    : {};
}
