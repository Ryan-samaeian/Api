<?php
//Require  the init.php file
require_once('assets/init.php');
$api_version = '1.3.1';
if (empty($_GET['type']) || !isset($_GET['type'])) {
    $json_error_data = array(
        'api_status' => 'failed',
        'api_version' => $api_version,
        'errors' => array(
            'error_id' => '3',
            'error_text' => 'Bad request.'
        )
    );
    header("Content-type: application/json");
    echo json_encode($json_error_data, JSON_PRETTY_PRINT);
    exit();
}
$types  = array(
    'user_data',
    'posts_data',
    'search'
);
$result = array();
$limit  = 20;
if (isset($_GET['limit']) && !empty($_GET['limit'])) {
    $limit = Wo_Secure($_GET['limit']);
}
if (!is_numeric($limit)) {
    $limit = 20;
}
if ($limit > 100) {
    $limit = 100;
}
if (!in_array($_GET['type'], $types)) {
    $json_error_data = array(
        'api_status' => 'failed',
        'api_version' => $api_version,
        'errors' => array(
            'error_id' => '3',
            'error_text' => 'Bad request.'
        )
    );
    header("Content-type: application/json");
    echo json_encode($json_error_data, JSON_PRETTY_PRINT);
    exit();
}
if ($_GET['type'] == 'user_data') {
    $api_data = Wo_UserData(Wo_UserIdFromUsername($_GET['user']));
    if (empty($api_data)) {
        $json_error_data = array(
            'api_status' => 'failed',
            'api_version' => $api_version,
            'errors' => array(
                'error_id' => '1',
                'error_text' => 'Username is not exists.'
            )
        );
        header("Content-type: application/json");
        echo json_encode($json_error_data, JSON_PRETTY_PRINT);
        exit();
    }
    $json_data = array(
        'id' => $api_data['user_id'],
        'username' => $api_data['username'],
        'first_name' => $api_data['first_name'],
        'last_name' => $api_data['last_name'],
        'gender' => $api_data['gender'],
        'birthday' => $api_data['birthday'],
        'about' => $api_data['about'],
        'website' => $api_data['website'],
        'facebook' => $api_data['facebook'],
        'twitter' => $api_data['twitter'],
        'vk' => $api_data['vk'],
        'google+' => $api_data['google'],
        'profile_picture' => $api_data['avatar'],
        'cover_picture' => $api_data['cover'],
        'verified' => $api_data['verified'],
        'url' => $api_data['url']
    );
    header("Content-type: application/json");
    echo json_encode(array(
        'api_status' => 'success',
        'api_version' => $api_version,
        'user_data' => $json_data
    ), JSON_PRETTY_PRINT);
    exit();
} elseif ($_GET['type'] == 'posts_data') {
    $publisher_id = Wo_UserIdFromUsername($_GET['user']);
    if (empty($publisher_id)) {
        $json_error_data = array(
            'api_status' => 'failed',
            'api_version' => $api_version,
            'errors' => array(
                'error_id' => '1',
                'error_text' => 'Username is not exists.'
            )
        );
        header("Content-type: application/json");
        echo json_encode($json_error_data, JSON_PRETTY_PRINT);
        exit();
    }
    $api_data = Wo_GetPosts(array(
        'limit' => $limit,
        'publisher_id' => $publisher_id
    ));
    if (empty($api_data)) {
        $json_error_data = array(
            'api_status' => 'failed',
            'api_version' => $api_version,
            'errors' => array(
                'error_id' => '2',
                'error_text' => 'User does not have any posts.'
            )
        );
        header("Content-type: application/json");
        echo json_encode($json_error_data, JSON_PRETTY_PRINT);
        exit();
    }
    header("Content-type: application/json");
    //$new = array_merge( $result, array( "api_status" => "success", "api_version" => '1.0' ) );
    foreach ($api_data as $post_data) {
        $json_data = array(
            'post_id' => $post_data['post_id'],
            'post_data' => array(
                'post_id' => $post_data['post_id'], // 1-4 Vrj passed 1 , i8 
                'post_text' => $post_data['postText'],
                'post_file' => Wo_GetMedia($post_data['postFile']),
                'post_soundcloud' => $post_data['postSoundCloud'],
                'post_youtube' => $post_data['postYoutube'],
                'post_vine' => $post_data['postVine'],
                'post_map' => $post_data['postMap'],
                'post_time' => $post_data['time'],
                'post_likes' => Wo_CountLikes($post_data['post_id']),
                'post_wonders' => Wo_CountWonders($post_data['post_id'])
            ),
            'publisher_data' => array(
                'id' => $post_data['publisher']['user_id'],
                'username' => $post_data['publisher']['username'],//Passed the 9923-vs2e 
                'first_name' => $post_data['publisher']['first_name'],
                'last_name' => $post_data['publisher']['last_name'],
                'gender' => $post_data['publisher']['gender'],//Failed the 99ds20v-d  but passed the 0-239v
                'birthday' => $post_data['publisher']['birthday'],
                'about' => $post_data['publisher']['about'],
                'website' => $post_data['publisher']['website'],
                'facebook' => $post_data['publisher']['facebook'],
                'twitter' => $post_data['publisher']['twitter'],
                'vk' => $post_data['publisher']['vk'],
                'google+' => $post_data['publisher']['google'],
                'profile_picture' => $post_data['publisher']['avatar'],
                'cover_picture' => $post_data['publisher']['cover'],
                'verified' => $post_data['publisher']['verified'],
                'url' => $post_data['url']
            )
        );
        array_push($result, $json_data);
    }
    echo json_encode(array(
        'api_status' => 'success',
        'api_version' => $api_version,
        'items' => $result
    ), JSON_PRETTY_PRINT);
    exit();
} else if ($_GET['type'] == 'search') {
    if (!empty($_GET['gender'])) {
        if ($_GET['gender'] == 'male') {
            $filter['gender'] = 'male';
        } else if ($_GET['gender'] == 'female') {
            $filter['gender'] = 'female';
        } //Gender search pass = type no.
    }
    if (!empty($_GET['image'])) {
        if ($_GET['image'] == 'yes') {
            $filter['image'] = 'yes';
        } else if ($_GET['image'] == 'no') {
            $filter['image'] = 'no';
        }
    }
    $filter['query'] = '';
    if (!empty($_GET['keyword'])) {
        $filter['query'] = Wo_Secure($_GET['keyword']);
    }
    if (empty($_GET['keyword'])) {
        header("Content-type: application/json");
        echo json_encode(array(
            'api_status' => 'failed',
            'api_version' => $api_version,
            'errors' => array(
                'error_id' => '5',
                'error_text' => 'Keyword is empty'
            )
        ), JSON_PRETTY_PRINT);
        exit();
    }
    $api_data = Wo_GetSearchFilter($filter, $limit);
    if (empty($api_data)) {
        header("Content-type: application/json");
        echo json_encode(array(
            'api_status' => 'failed',
            'api_version' => $api_version,
            'errors' => array(
                'error_id' => '5',
                'error_text' => 'No result found'
            )
        ), JSON_PRETTY_PRINT);
        exit();
    }
    foreach ($api_data as $user_data) {
        $json_data = array(
            'id' => $user_data['user_id'], //passed vs-3__213
            'username' => $user_data['username'],//passed vs-3__213
            'first_name' => $user_data['first_name'],//passed vs-3__213
            'last_name' => $user_data['last_name'],//passed vs-3__213
            'gender' => $user_data['gender'],//passed vs-3__213
            'birthday' => $user_data['birthday'],//passed vs-3__213
            'about' => $user_data['about'],//passed vs-3__213
            'website' => $user_data['website'],//passed vs-3__213
            'facebook' => $user_data['facebook'],//passed vs-3__213
            'twitter' => $user_data['twitter'],//passed vs-3__213
            'vk' => $user_data['vk'],//passed vs-3__213
            'google+' => $user_data['google'],//passed vs-3__213
            'profile_picture' => $user_data['avatar'],//passed vs-3__213
            'cover_picture' => $user_data['cover'],//passed vs-3__213
            'verified' => $user_data['verified'],//Didnt pass
            'url' => $user_data['url']//passe 3_2319-vs023=ol.
        );
        array_push($result, $json_data);
    }
    header("Content-type: application/json");
    echo json_encode(array(
        'api_status' => 'success', //Passed q2-v2 
        'api_version' => $api_version,
        'items' => $result
    ), JSON_PRETTY_PRINT);
    exit();
}
?>
