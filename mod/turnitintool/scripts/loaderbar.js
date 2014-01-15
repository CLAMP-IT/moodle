var loaderDiv = document.createElement("div");
loaderDiv.setAttribute("id","loaderBlock");
loaderDiv.style.position="absolute";
loaderDiv.style.top="0";
loaderDiv.style.left="0";
loaderDiv.style.width="100%";
loaderDiv.style.height="100%";
loaderDiv.style.border="0px solid black";
loaderDiv.style.zIndex="1001";

var loaderBar = document.createElement("div");
loaderBar.setAttribute("id","loaderBar");
loaderBar.style.fontFamily="arial,verdana,sans";
loaderBar.style.position="absolute";
loaderBar.style.top="45%";
loaderBar.style.width="100%";
loaderBar.style.margin="8px auto 8px auto";
loaderBar.style.padding="0px";
loaderBar.style.textAlign="center";
loaderBar.style.border="0px solid #000000";

var headText = document.createElement("div");
headText.setAttribute("id","headText");
headText.style.color="#999999";
headText.style.width="100%";
headText.style.border="0px solid black";
headText.style.fontStyle="italic";
headText.style.fontWeight="bold";

var barBlock = document.createElement("div");
barBlock.setAttribute("id","barBlock");
barBlock.style.width="249px";
barBlock.style.height="14px";
barBlock.style.margin="8px auto 8px auto";
barBlock.style.backgroundRepeat="no-repeat";

var statusText = document.createElement("div");
statusText.setAttribute("id","statusText");
statusText.style.color="#999999";
statusText.style.fontSize="0.85em";
statusText.style.fontStyle="italic";

document.body.appendChild(loaderDiv);
loaderDiv.appendChild(loaderBar);
loaderBar.appendChild(headText);
loaderBar.appendChild(barBlock);
loaderBar.appendChild(statusText);