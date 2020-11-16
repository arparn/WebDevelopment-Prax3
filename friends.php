<?php
require_once 'includes/utils.php';
require_once 'includes/auth.php';
require_once 'includes/db.php';

if (!is_logged_in()) {
    redirect('index.php');
}

$all_users = all_other_users($_SESSION['id']);
$input = post('name');
$needed_people = [];
$friends = get_friends($_SESSION['id']);

function check_if_friends($my_id, $user_id) {
    $conn = db();
    $query = "SELECT * FROM friends WHERE (first_user_id = ? AND second_user_id = ?) OR (first_user_id = ? AND second_user_id = ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('iiii', $my_id, $user_id, $user_id, $my_id);
    if ($stmt->execute()) {
        $res = $stmt->get_result();
        $answer = $res->fetch_all();
        if(count($answer) === 1){
            return true;
        }
        else{
            return false;
        }
    } else {
        $_SESSION['message'] = "Cant get user";
        $_SESSION['type'] = "alert-danger";
        return 'error';
    }
}

function find_people($all_users, $input) {
    $needed_people = [];
    if ($input == "" || $input == " ") {
        return $needed_people;
    }
    foreach ($all_users as $user) {
        $check = strpos(strtoupper($user['1']), strtoupper($input));
        if ($check === false) {
            $check = strpos(strtoupper($user['5']), strtoupper($input));
        }
        if ($check !== false) {
            $needed_people[] = $user;
        }
    }
    return $needed_people;
}

if (post('action') == 'find_person') {
    $needed_people = find_people($all_users, $input);
}

if (post('action') == 'add_to_friends') {
    $conn = db();
    $query = 'INSERT INTO friends(first_user_id, second_user_id) VALUES("%d", "%d")';
    $query = sprintf($query, $_SESSION['id'], post('person_id'));
    mysqli_query($conn, $query) or die('Error');
    $_SESSION['message'] = "Friend Added!";
    $_SESSION['type'] = 'alert-success';
}
?>

<?php include 'components/header.php'; ?>
<!-- CONTENT -->

<div>
<h3>Search by name or city:</h3>
<form method="post">

    <input type="hidden" name="action" value="find_person">

    <div>
        <label>Name/City:
            <input type="text" name="name">
        </label>
    </div>

    <div>
        <input type="submit" value="Search">
    </div>

</form>
</div>

<div>
    <?php if (count($needed_people) !== 0) { ?>
        <h3>For your request people found (<?php count($needed_people)?>):</h3>
        <ul>
            <?php foreach ($needed_people as $person) { ?>
                <li>
                    <form method="post">
                        <div>
                            <?php if (!check_if_friends($_SESSION['id'], post('person_id'))) {?>
                            <input type="hidden" name="action" value="add_to_friends">
                            <input type="hidden" name="person_id" value="<?php echo $person['0']?>">
                            <p><?php echo $person['1']?></p>
                            <p><?php echo $person['5']?></p>
                            <p><?php echo $person['4']?></p>
                            <input type="submit" value="Add to friends">
                            <?php } else { ?>
                                <p><?php echo $person['1']?></p>
                                <p><?php echo $person['5']?></p>
                                <p><?php echo $person['4']?></p>
                                <p><?php echo $person['1']?> is already your friend.</p>
                            <?php } ?>
                        </div>
                    </form>
                </li>
            <?php } ?>
        </ul>
    <?php } ?>
</div>

<div>
    <?php if (count($friends) === 0) { ?>
        <h3>You have no friends yet.</h3>
    <?php } else { ?>
        <h3>Your Friends:</h3>
        <?php foreach ($friends as $friend) { ?>
            <p><?php echo $friend['1']?></p>
            <p><?php echo $friend['5']?></p>
            <p><?php echo $friend['4']?></p>
        <?php } ?>
    <?php } ?>
</div>

<!-- /CONTENT -->
<?php include 'components/footer.php'; ?>

