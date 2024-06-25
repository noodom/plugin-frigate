<?php

use frigate;
use log;

if (!isConnect('admin')) {
  throw new Exception('{{401 - Accès non autorisé}}');
}

?>


<div class="col-lg-12"><br>
  <br>
  <div class="input-group" style="margin-bottom:20px">
    <span class="input-group-btn">
      <a class="btn roundedLeft" id="gotoHome"><i class="fa fa-arrow-circle-left"></i> retour </a>
      <a class="btn btn-danger roundedRight" id="deleteAll"><i class="fa fa-trash"></i> supprimer tous les events visibles </a>
    </span>
  </div>
  <?php

  function formatDuration($seconds)
  {
    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds % 3600) / 60);
    $remainingSeconds = $seconds % 60;

    $formattedDuration = '';
    if ($hours > 0) {
      $formattedDuration .= $hours . 'h';
      $formattedDuration .= str_pad($minutes, 2, '0', STR_PAD_LEFT) . 'mn';
      $formattedDuration .= str_pad($remainingSeconds, 2, '0', STR_PAD_LEFT) . 's';
    } elseif ($minutes > 0) {
      $formattedDuration .= $minutes . 'mn';
      $formattedDuration .= str_pad($remainingSeconds, 2, '0', STR_PAD_LEFT) . 's';
    } else {
      $formattedDuration .= str_pad($remainingSeconds, 2, '0', STR_PAD_LEFT) . 's';
    }

    return $formattedDuration;
  }

  function timeElapsedString($datetime, $full = false)
  {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    $diff->w = floor($diff->d / 7);
    $diff->d -= $diff->w * 7;

    $string = array(
      'y' => 'année',
      'm' => 'mois',
      'w' => 'semaine',
      'd' => 'jour',
      'h' => 'heure',
      'i' => 'minute',
      's' => 'seconde',
    );

    foreach ($string as $k => &$v) {
      if ($diff->$k) {
        $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
      } else {
        unset($string[$k]);
      }
    }

    if (!$full) $string = array_slice($string, 0, 1);
    return $string ? 'il y a ' . implode(', ', $string) : 'à l\'instant';
  }

  $events = frigate::showEvents();

  echo '<div class="col-sm-10 flex-container">';
  echo '<a class="btn btn-info button-xs" id="selectAllCameras" style="margin-right:10px"><i class="fas fa-check"></i> {{Tout}}</a>';
  echo '<a class="btn btn-info button-xs" id="deselectAllCameras" style="margin-right:20px"><i class="fas fa-times"></i> {{Aucun}}</a>';
  echo '<div class="checkbox-container">';
  $selectedCameras = isset($_GET['cameras']) ? explode(',', $_GET['cameras']) : [];
  $cameras = array_unique(array_column($events, 'camera'));
  foreach ($cameras as $camera) {
    $isChecked = empty($selectedCameras) || in_array($camera, $selectedCameras);
    echo '<label><input type="checkbox" class="eqLogicAttr cameraFilter" value="' . $camera . '" ' . ($isChecked ? 'checked' : '') . '>';
    echo '<span class="custom-checkbox"></span> ' . ucfirst($camera) . '</label>';
  }
  echo '</div>';
  echo '</div>';

  echo '<div class="col-sm-10 flex-container">';
  echo '<a class="btn btn-info button-xs" id="selectAllLabels" style="margin-right:10px"><i class="fas fa-check"></i> {{Tout}}</a>';
  echo '<a class="btn btn-info button-xs" id="deselectAllLabels" style="margin-right:20px"><i class="fas fa-times"></i> {{Aucun}}</a>';
  echo '<div class="checkbox-container">';
  $selectedLabels = isset($_GET['categories']) ? explode(',', $_GET['categories']) : [];
  $labels = array_unique(array_column($events, 'label'));
  foreach ($labels as $label) {
    $isChecked = empty($selectedLabels) || in_array($label, $selectedLabels);
    echo '<label><input type="checkbox" class="eqLogicAttr labelFilter" value="' . $label . '" ' . ($isChecked ? 'checked' : '') . '> ';
    echo '<span class="custom-checkbox"></span> ' . ucfirst($label) . '</label>';
  }
  echo '</div>';
  echo '</div>';

  echo '<div class="col-sm-12" style="margin-bottom:10px">';
  echo '<div class="col-sm-4 datetime-container">
        <label>Entre <input type="datetime-local" id="startDate"></label>
        <label>et <input type="datetime-local" id="endDate"></label>
        <label>Ou de </label>
    </div>';

  $selectedTimeFilter = isset($_GET['delai']) ? $_GET['delai'] : '';

  $timeFilters = [
    ''    => 'Toutes les dates',
    '1h'  => 'Moins d\'une heure',
    '2h'  => 'Moins de deux heures',
    '6h'  => 'Moins de six heures',
    '12h' => 'Moins de douze heures',
    '1j'  => 'Moins d\'un jour',
    '2j'  => 'Moins de deux jours',
    '1s'  => 'Moins d\'une semaine'
  ];

  echo '<div class="col-sm-8 radio-container">';
  foreach ($timeFilters as $value => $label) {
    $isChecked = $value === $selectedTimeFilter;
    echo '<label><input type="radio" name="timeFilter" value="' . $value . '" ' . ($isChecked ? 'checked' : '') . '>';
    echo '<span class="custom-radio"></span> ' . $label . '</label>';
  }
  echo '</div>';
  echo '</div>';

  echo '<div>';
  foreach ($events as $event) {
    //div globale start
    echo '<div data-date="' . $event['date'] .  '" data-camera="' . $event['camera'] . '" data-label="' . $event['label'] . '" data-id="' . $event['id'] . '" class="frigateEventContainer col-lg-4 ">';
    echo '<div class="col-lg-12 frigateEvent">';
    // div img
    echo '<div>';
    echo '<img class="imgSnap" src="' . $event['img'] . '"/>';
    echo '</div>';
    // div texte
    echo '<div class="eventText">';
    $timeElapsed = timeElapsedString($event['date']);
    echo '<span class="inline-title">' . $event['label'] . '</span><span class="inline-subtitle duration"> ' . $timeElapsed . '</span><br/><br/>';
    echo '<i class="fas fa-minus-square"></i><span>  ' . $event['label'] . ' <div class="percentage" data-percentage="' . $event['top_score'] . '">' . $event['top_score'] . '%</div></span><br>';

    $cameraFound = false;
    $cameraId = 0;
    try {
      $attribut = 'name';
      $valeurRecherchee = $event['camera'];
      $frigateCamera = eqLogic::byLogicalId('eqFrigateCamera_' . $valeurRecherchee, 'frigate', false);
      if ($frigateCamera != false) {
        $cameraFound = true;
        $cameraId = $frigateCamera->getId();
      }
    } catch (Exception $e) {
      //echo "Erreur : " . $e->getMessage();
    }

    if ($cameraFound) {
      echo '<a onclick="gotoCamera(\'' . $cameraId . '\')" title="Afficher la page de la caméra">';
    }
    echo '<i class="fas fa-video"></i><span>  ' . $event['camera'] . '</span>';
    if ($cameraFound) {
      echo '</a>';
    }
    echo '<br>';

    $formattedDuration = '<div class=\'duration\'>' . formatDuration($event['duree']) . '</div>';
    $formattedDurationTitle = '<div class=\'duration durationTitle\'>' . formatDuration($event['duree']) . '</div>';

    echo '<i class="fas fa-clock"></i><span>  ' . $event['date'] . ' ' . $formattedDuration . '</span>';
    echo '</div>';
    // div buttons
    echo '<div class="eventBtns"';
    if ($event['hasSnapshot'] == 1) echo ' data-snapshot="' . $event['snapshot'] . '"';
    if ($event['hasClip'] == 1) echo ' data-video="' . $event['clip'] . '"';
    echo ' data-title="' . $event['label'] . ' <div class=\'percentage percentageTitle\' data-percentage=\'' . $event['top_score'] . '\'>' . $event['top_score'] . ' %</div> - ' . $event['camera'] . ' - ' . $event['date'] . ' ' . $formattedDurationTitle . '"';
    echo '>';
    if ($event['hasSnapshot'] == 1) {
      echo '<button class="hover-button snapshot-btn" title="Voir le snapshot">';
      echo '<i class="fas fa-camera"></i>';
      echo '</button>';
    }
    if ($event['hasClip'] == 1) {
      echo '<button class="hover-button video-btn" title="Voir le clip">';
      echo '<i class="fas fa-film"></i>';
      echo '</button>';
    }
    echo '<button class="hover-button" onclick="deleteEvent(\'' . $event['id'] . '\')" title="Supprimer l\'event sur votre serveur frigate">';
    echo '<i class="fas fa-trash"></i>';
    echo '</button>';
    echo '</div>';
    // div globale end
    echo '</div>';
    echo '</div>';
  }
  echo '</div>';

  ?>

  <div id="mediaModal" class="modal">
    <div class="modal-content">
      <span class="close">&times;</span>

      <div class="modal-header">
        <h2 id="mediaTitle"></h2>
        <div class="button-container">
          <button id="showVideo" class="hidden-btn">Voir la vidéo</button>
          <button id="showImage" class="hidden-btn">Voir la snapshot</button>
        </div>
      </div>
      <div class="media-container">
        <div class="video-container active">
          <video id="videoPlayer" width="100%" controls autoplay>
            <source id="videoSource" src="" type="video/mp4">
            Votre navigateur ne supporte pas la balise vidéo.
          </video>
        </div>
        <div class="image-container">
          <img id="snapshotImage" src="" alt="Snapshot" width="100%">
        </div>
      </div>
    </div>
  </div>

</div>


<style>
  .frigateEvent {
    display: flex;
    background-color: rgb(var(--defaultBkg-color));
    margin-bottom: 10px;
    border-radius: 10px;
  }

  .imgSnap {
    flex: 0 0 auto;
    position: relative;
    background-color: rgb(var(--defaultBkg-color));
    margin-left: -15px;
    height: 125px;
    border-bottom-left-radius: 10px;
    border-top-left-radius: 10px;

  }

  .eventText {
    flex: 1 1 auto;
    position: relative;
    margin-left: 20px;

  }

  .eventBtns {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    margin-left: auto;

  }

  .hover-button {
    background: none;
    border: none;
    color: rgb(var(--defaultText-color));
    font-size: 20px;
  }

  .hover-button:hover~.hover-image {
    display: block;
  }

  .hover-button-container {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    /* Align buttons to the right */
  }

  .eventHidden {
    display: none;
  }

  .modal {
    display: none;
    position: fixed;
    z-index: 2;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0, 0, 0, 0.7);
    padding-top: 60px;
  }

  .modal-content {
    background-color: #fefefe;
    margin: 5% auto;
    padding: 20px;
    border: 1px solid #888;
    width: 80%;
    position: relative;
  }

  .modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
  }

  .modal-header h2 {
    flex: 1;
    margin: 0;
  }

  .button-container {
    display: flex;
    gap: 10px;
  }

  .media-container {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
  }

  .close {
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
  }

  .close:hover,
  .close:focus {
    color: black;
    text-decoration: none;
    cursor: pointer;
  }

  .video-container,
  .image-container {
    display: none;
  }

  .video-container,
  .image-container {
    width: 100%;
  }

  .video-container video,
  .image-container img {
    width: 100%;
    display: block;
  }

  .active {
    display: block;
  }

  .hidden-btn {
    display: none;
  }

  .duration {
    display: inline-block;
    padding: 5px;
    border-radius: 5px;
    background-color: #B9A9A7;
    color: black;
    font-weight: bold;
    margin-top: 5px;
    height: 20px;
    line-height: 10px;
    margin-left: 10px;
  }

  .durationTitle {
    height: 30px;
    line-height: 15px;
  }

  .percentage {
    display: inline-block;
    padding: 5px;
    border-radius: 5px;
    color: black;
    font-weight: bold;
    margin-top: 5px;
    height: 20px;
    line-height: 10px;
    margin-left: 20px;
  }

  .percentageTitle {
    height: 30px;
    line-height: 20px;
  }

  .percentage[data-percentage="100"] {
    background-color: #4caf50;
  }

  .percentage[data-percentage^="9"] {
    background-color: #4caf50;
  }

  .percentage[data-percentage^="8"] {
    background-color: #66bb6a;
  }

  .percentage[data-percentage^="7"],
  .percentage[data-percentage^="6"] {
    background-color: #ff9800;
  }

  .percentage[data-percentage^="5"] {
    background-color: #f44336;
  }

  .percentage[data-percentage^="4"],
  .percentage[data-percentage^="3"],
  .percentage[data-percentage^="2"],
  .percentage[data-percentage^="1"],
  .percentage[data-percentage^="0"] {
    background-color: #b71c1c;
  }

  .inline-title {
    font-size: 1.5em;
    font-weight: bold;
  }

  .inline-subtitle {
    font-size: 1em;
    color: black;
    margin-left: 18px;
  }

  .datetime-container {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 5px;
  }

  .datetime-container label {
    font-size: 16px;
    font-weight: bold;
    color: #333;
  }

  .datetime-container input[type="datetime-local"] {
    border: 2px solid #ccc;
    border-radius: 4px;
    padding: 5px 10px;
    font-size: 14px;
    color: #333;
    transition: border-color 0.3s;
  }

  .datetime-container input[type="datetime-local"]:focus {
    border-color: #007BFF;
    outline: none;
  }

  .radio-container {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 10px;
    margin-bottom: 5px;
  }

  .radio-container label {
    display: flex;
    align-items: center;
    font-size: 14px;
    color: #333;
    cursor: pointer;
  }

  .radio-container input[type="radio"] {
    display: none;
  }

  .custom-radio {
    width: 20px;
    height: 20px;
    border: 2px solid #ccc;
    border-radius: 50%;
    margin-right: 10px;
    position: relative;
    transition: border-color 0.3s;
  }

  .radio-container input[type="radio"]:checked+.custom-radio {
    border-color: #fa8b09;
  }

  .radio-container input[type="radio"]:checked+.custom-radio::after {
    content: "";
    width: 10px;
    height: 10px;
    background-color: #fa8b09;
    border-radius: 50%;
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
  }

  .flex-container {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 10px;
    margin-bottom: 5px;
  }

  .checkbox-container {
    display: grid;
    grid-template-columns: repeat(8, 1fr);
    gap: 10px;
    flex: 1;
  }

  .checkbox-container label {
    display: flex;
    align-items: center;
    font-size: 14px;
    color: #333;
    cursor: pointer;
    position: relative;
    padding-left: 30px;
  }

  .checkbox-container input[type="checkbox"] {
    display: none;
  }

  .custom-checkbox {
    position: absolute;
    left: 0;
    top: 0;
    width: 20px;
    height: 20px;
    border: 2px solid #ccc;
    border-radius: 4px;
    transition: border-color 0.3s, background-color 0.3s;
  }

  .checkbox-container input[type="checkbox"]:checked+.custom-checkbox {
    border-color: #fa8b09;
    background-color: #fa8b09;
  }

  .custom-checkbox::after {
    content: "";
    position: absolute;
    display: none;
    left: 5px;
    top: 2px;
    width: 5px;
    height: 10px;
    border: solid white;
    border-width: 0 2px 2px 0;
    transform: rotate(45deg);
  }

  .checkbox-container input[type="checkbox"]:checked+.custom-checkbox::after {
    display: block;
  }

  .btn {
    margin-bottom: 10px;
  }
</style>

<?php include_file('desktop', 'events', 'js', 'frigate'); ?>