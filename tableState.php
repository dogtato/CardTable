<?php
session_start();
// make a singleton to store the connection and stuff
require_once "../../db_connect/cards.inc";

$connection = GetDatabaseConnection();
$getStateQuery = $connection->prepare("SELECT player, zone, type, id, imageUrl,
                                      xPos, yPos, rotation, ordering FROM CurrentState
                                      WHERE room = ?");
$addQuery = $connection->prepare("INSERT INTO CurrentState
                                 (room, player, zone, type, id,
                                 imageUrl, xPos, yPos)
                                 SELECT ?, ?, ?, ?, ?, ?, ?, ?");
$updateQuery = $connection->prepare("UPDATE CurrentState SET
                                    zone = ?, xPos = ?, yPos = ?, rotation = ?,
                                    ordering = ?
                                    WHERE room = ? AND player = ? AND
                                    type = ? AND id = ?");
$updateDeckOrderQuery = $connection->prepare("UPDATE CurrentState SET
                                    ordering = ?
                                    WHERE room = ? AND player = ? AND
                                    id = ? AND zone = 'deck'");
$removeQuery = $connection->prepare("DELETE FROM CurrentState
                                    WHERE room = ? AND player = ? AND
                                    type = ? AND id = ?");
$removePlayerQuery = $connection->prepare("DELETE FROM CurrentState
                                          WHERE room = ? AND player = ?");
$markAsUpdatedQuery = $connection->prepare("INSERT INTO LastRoomUpdate
                                           (room) SELECT ?");
$checkForUpdateQuery = $connection->prepare("SELECT id FROM LastRoomUpdate
                                            WHERE room = ? AND id > ?
                                            ORDER BY id DESC");

// for debugging
if ($_SERVER['REQUEST_METHOD'] === 'GET')
{
    $_POST = $_GET;
}
// var_dump($_POST);

if ($_POST["action"] === "get_state")
{
    $checkForUpdateQuery->bind_param("si",
                                     $room,
                                     $last_update_id);
    $_SESSION["room"] = $_POST["room"];
    $_SESSION["last_update_id"] = $_POST["last_update_id"];
    $room = $_SESSION["room"];
    $last_update_id = $_SESSION["last_update_id"];
    session_write_close();
    // wait for new data
    $totalTimeSlept = 0;
    while (true)
    {
        $checkForUpdateQuery->execute();
        $checkForUpdateQuery->bind_result($changeId);
        if ($checkForUpdateQuery->fetch())
        {
            $checkForUpdateQuery->close();
            break;
        }
        // handle case of new rooms with no updates
        elseif ($last_update_id == "-1")
        {
            $checkForUpdateQuery->close();
            $markAsUpdatedQuery->bind_param("s", $_POST["room"]);
            $markAsUpdatedQuery->execute();
            $lastUpdateId = $connection->insert_id;
            $markAsUpdatedQuery->close();
            $connection->close();
            echo '{"last_update_id":"$lastUpdateId"}';
            echo json_encode(array("change_id" => $lastUpdateId,
                                   "results" => []));
            return;
        }
        $totalTimeSlept += 0.2;
        // max wait time
        if ($totalTimeSlept > 60)
        {
            $checkForUpdateQuery->close();
            $connection->close();
            echo '{"no_changes":"true"}';
            return;
        }
        usleep(200000);
        session_start();
        // just end and wait for a resend if room changes
        if ($room !== $_SESSION["room"])
        {
            session_write_close();
            echo '{"no_changes":"true"}';
            return;
        }
        session_write_close();
    }

    $getStateQuery->bind_param("s", $_POST["room"]);
    $getStateQuery->execute();
    $getStateQuery->bind_result($player, $zone, $type, $id, $imageUrl,
                                $xPos, $yPos, $rotation, $ordering);
    $results = [];
    while ($getStateQuery->fetch())
    {
        $row = [
            "player" => $player,
            "zone" => $zone,
            "type" => $type,
            "id" => $id,
            "imageUrl" => $imageUrl,
            "xPos" => $xPos,
            "yPos" => $yPos,
            "rotation" => $rotation,
            "ordering" => $ordering
        ];
        array_push($results, $row);
    }
    echo json_encode(array("change_id" => $changeId,
                           "results" => $results));
}
else
{
    if ($_POST["action"] === "change_room")
    {
        $_SESSION["room"] = $_POST["room"];
        $_SESSION["last_update_id"] = -1;
        session_write_close();
        $connection->close();
        return;
    }
    elseif ($_POST["action"] === "add")
    {
        for ($i=0; $i < count($_POST["id"]); $i++)
        {
            $addQuery->bind_param("ssssisii",
                                  $_POST["room"],
                                  $_POST["player"],
                                  $_POST["zone"],
                                  $_POST["type"],
                                  $_POST["id"][$i],
                                  $_POST["image_url"][$i],
                                  $_POST["x_pos"][$i],
                                  $_POST["y_pos"][$i]);
            $addQuery->execute();
        }
        $addQuery->close();
    }
    elseif ($_POST["action"] === "update")
    {
        $updateQuery->bind_param("siiiisssi",
                                 $_POST["zone"],
                                 $_POST["x_pos"],
                                 $_POST["y_pos"],
                                 $_POST["rotation"],
                                 $_POST["ordering"],
                                 $_POST["room"],
                                 $_POST["player"],
                                 $_POST["type"],
                                 $_POST["id"]);
        $updateQuery->execute();
        $updateQuery->close();
    }
    elseif ($_POST["action"] === "update_deck_order")
    {
        for ($i=0; $i < count($_POST["id"]); $i++)
        {
            $updateDeckOrderQuery->bind_param("issi",
                                  $_POST["ordering"][$i],
                                  $_POST["room"],
                                  $_POST["player"],
                                  $_POST["id"][$i]);
            $updateDeckOrderQuery->execute();
        }
        $updateDeckOrderQuery->close();
    }
    elseif ($_POST["action"] === "remove")
    {
        $removeQuery->bind_param("sssi",
                                 $_POST["room"],
                                 $_POST["player"],
                                 $_POST["type"],
                                 $_POST["id"]);
        $removeQuery->execute();
        $removeQuery->close();
    }
    elseif ($_POST["action"] === "remove_all")
    {
        $removePlayerQuery->bind_param("ss",
                                       $_POST["room"],
                                       $_POST["player"]);
        $removePlayerQuery->execute();
        $removePlayerQuery->close();
    }

    $markAsUpdatedQuery->bind_param("s", $_POST["room"]);
    $markAsUpdatedQuery->execute();
    $lastUpdateId = $connection->insert_id;
    echo "{\"last_update_id\":\"{$lastUpdateId}\"}";
}

$connection->close();
?>
