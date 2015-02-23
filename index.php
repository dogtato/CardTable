<!DOCTYPE html>
<html lang="en-US">
<meta charset="utf-8">

<head>
  <link rel="stylesheet" type="text/css" href="./style.css">
  <!-- third party javascript -->
  <script src="./js/jquery-2.1.3.js"></script>
  <script src="./js/jquery.mousewheel.js"></script>
  <script src="./js/jquery.form.js"></script>
  <script src="./js/jquery.csv-0.71.js"></script>

  <script src="./js/d3.js"></script>

  <script src="./js/drawPlayArea.js"></script>
</head>

<body>

<svg id="playAreaSVG">
  <g id="tableGraphic"></g>
  <g id="cardFrames"></g>
  <g id="players"></g>
  <g id="markers"></g>
  <g id="enlargedCard"></g>
  <g id="cardButtons"></g>
</svg>

<div id="coordDisplay" class="overlayItem">x: y:</div>
<div id="motionDisplay" class="overlayItem">dx: dy:</div>
<div id="motionDisplayCorrected" class="overlayItem">dx: dy:</div>

<div id="gameControls">
  <div id="roomNameBox">
    <h4>Room:</h4>
    <input id="roomName" type="text" value="Room">
    <button id="setRoom">Join</button>
  </div>

  <div id="playerNameBox">
    <h4>Player:</h4>
    <select id="playerSelect"></select>
    <input id="playerName" type="text" value="defaultPlayer">
    <div><button id="setPlayer">Change Player</button></div>
    <div><button id="setName">Change Name</button></div>
  </div>

  <div id="deckBox" class="overlayItem inline">
    <H2>Deck (<span id="deckCount">0</span> cards)</H2>
    <button id="drawCard">Draw</button>
    <button id="showDeckList">List</button>
    <button id="shuffleDeck">Shuffle</button>
    <button id="resetPlayer">Reset</button>

    <div id="deckListBox">
      <select id="deckList" size="15"></select>
      <div><button id="drawSelectedCard">Draw Selected</button></div>
    </div>

    <div id="loadDeckBox">
      <div id="loadDeckHeader" class="links">Load From CSV</div>
      <div id="loadDeckForm">
        <div>Required Columns:</div>
        <div>name, image_url, count</div>
        <div> (case-sensitive but any order)</div>
        <textarea type="text" id="deckCSV" value="" rows="10" cols="25"></textarea>
        <div><button id="loadDeck">Load</button></div>
      </div>
    </div>
  </div>

  <div id="createMarkerBox">
    <h4>Marker Text:</h4>
    <input id="markerText" type="text" value="+1/+1">
    <button id="createMarker">Create</button>
  </div>

  <div id="settingsHeader" class="links">Settings</div>
  <div id="settingsBox">
    <div>
      Table Image:
      <input id="tableImageUrl" type="text" value="">
      <button id="setTableImageUrl">Set</button>
    </div>
    <div>
      Table Image Scale:
      <input id="tableImageScale" type="text" size="1" value="1">
      <button id="setTableImageScale">Set</button>
    </div>
    <div>
      Table Radius:
      <input id="tableRadius" type="text" size="1" value="750">
      <button id="setTableRadius">Set</button>
    </div>
    <div>
      Marker Size:
      <input id="markerSize" type="text" size="1" value="3">
      <button id="setMarkerSize">Set</button>
    </div>
    <div>
      Enlarged Card Size:
      <input id="cardSize" type="text" size="1" value="1">
      <button id="setCardSize">Set</button>
    </div>
    <div>
      Deck Deal Point:
      x<input id="deckDealX" type="text" size="1" value="100">
      y<input id="deckDealY" type="text" size="1" value="100">
      <button id="setDeckDealPoint">Set</button>
    </div>
  </div>
</div>

<table id="scoreBoard" class="overlayItem"></table>

<div id="mouseoverBox" class="overlayItem"></div>

</body>
</html>
