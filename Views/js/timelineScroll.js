function syncTimelineScrollBars() {
	document.querySelectorAll(".timeline-scroll-wrap").forEach((wrap) => {
		const top = wrap.querySelector(".timeline-scroll-track-top");
		const row = wrap.querySelector(".timeline-scroll-row");
		const spacer = wrap.querySelector(".timeline-scroll-spacer");
		if (!top || !row || !spacer) return;

		spacer.style.width = `${row.scrollWidth}px`;
		if (top.dataset.synced) return;
		top.addEventListener("scroll", () => (row.scrollLeft = top.scrollLeft));
		row.addEventListener("scroll", () => (top.scrollLeft = row.scrollLeft));
		top.dataset.synced = "true";
	});
}

document.addEventListener("DOMContentLoaded", syncTimelineScrollBars);
document.addEventListener("shown.bs.tab", syncTimelineScrollBars);
window.addEventListener("resize", syncTimelineScrollBars);
