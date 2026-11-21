setTimeout(() => {
	document.querySelectorAll(".cv-unannounced").forEach((el) => {
		Object.assign(el.style, {
			position: "absolute",
			width: "1px",
			height: "1px",
			margin: "-1px",
			padding: "0",
			overflow: "hidden",
			clipPath: "inset(50%)",
			whiteSpace: "nowrap",
			border: "0",
		});
	});
}, 500);
