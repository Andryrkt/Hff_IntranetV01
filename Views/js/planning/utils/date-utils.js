export function formatDate(date) {
    const d = new Date(date);
    return `${d.getDate().toString().padStart(2, "0")}/${(d.getMonth() + 1)
      .toString()
      .padStart(2, "0")}/${d.getFullYear()}`;
  }
  
  export function formatDateOrEmpty(date) {
    const formattedDate = formatDate(date);
    if (["01/01/1970", "01/01/1900", ""].includes(formattedDate)) {
      return "";
    }
    return formattedDate;
  }
  