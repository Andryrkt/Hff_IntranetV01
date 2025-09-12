document.addEventListener("DOMContentLoaded", function () {
  const iframe = document.getElementById("iframe-conge");
  iframe.addEventListener("load", function () {
    const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
    iframeDoc.getElementById("go-to-classic-forms-button").remove();
  });
});
