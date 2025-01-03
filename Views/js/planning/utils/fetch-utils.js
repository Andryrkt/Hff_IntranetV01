export function fetchData(url, signal) {
  return fetch(url, { signal })
    .then(handleFetchResponse)
    .catch(handleFetchError);
}

export function handleFetchResponse(response) {
  if (!response.ok) {
    throw new Error("Network response was not ok");
  }
  return response.json();
}

export function handleFetchError(error) {
  if (error.name === "AbortError") {
    console.log("Requête annulée !");
  } else {
    console.error("There was a problem with the fetch operation:", error);
  }
}
