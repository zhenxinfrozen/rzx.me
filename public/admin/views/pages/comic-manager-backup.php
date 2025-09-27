<?php<?php<?php

// 防止直接访问

if (!defined('ADMIN_ACCESS')) {// 防止直接访问// 防止直接访问

    header('Location: /admin');

    exit;if (!defined('ADMIN_ACCESS')) {if (!defined('ADMIN_ACCESS')) {

}

    header('Location: /admin');    header('Location: /admin');

require_once __DIR__ . '/../../../../app/Models/comic_data.php';

    exit;    exit;

// 处理表单提交

$message = '';}}

$messageType = '';



if ($_POST) {

    $action = $_POST['action'] ?? '';require_once __DIR__ . '/../../../../app/Models/comic_data.php';require_once __DIR__ . '/../../../../app/Models/comic_data.php';

    

    switch ($action) {

        case 'add':

            $result = handleComicAdd();// 处理表单提交// 处理表单提交

            break;

        case 'edit':$message = '';$message = '';

            $result = handleComicEdit();

            break;$messageType = '';$messageType = '';

        case 'delete':

            $result = handleComicDelete();

            break;

    }if ($_POST) {if ($_POST) {

    

    if ($result) {    $action = $_POST['action'] ?? '';    $action = $_POST['action'] ?? '';

        $message = $result['message'];

        $messageType = $result['type'];        

    }

}    switch ($action) {    switch ($action) {



// 获取所有漫画数据        case 'add':        case 'add':

$allComics = get_all_comics_data();

            $result = handleComicAdd();            $result = handleComicAdd();

// 处理图片上传的函数

function handleImageUpload($fileKey, $folder = 'comic', $isIcon = false, $iconType = '') {            break;            break;

    $uploadedFiles = [];

            case 'edit':        case 'edit':

    if (!isset($_FILES[$fileKey])) {

        return $uploadedFiles;            $result = handleComicEdit();            $result = handleComicEdit();

    }

                break;            break;

    $files = $_FILES[$fileKey];

            case 'delete':        case 'delete':

    // 根据类型设置上传目录

    if ($isIcon) {            $result = handleComicDelete();            $result = handleComicDelete();

        $uploadDir = __DIR__ . '/../../../assets/images/' . $folder . '/thumbs/';

        $webPath = '/assets/images/' . $folder . '/thumbs/';            break;            break;

    } else {

        $uploadDir = __DIR__ . '/../../../assets/images/' . $folder . '/';    }    }

        $webPath = '/assets/images/' . $folder . '/';

    }        

    

    if (!is_dir($uploadDir)) {    if ($result) {    if ($result) {

        mkdir($uploadDir, 0755, true);

    }        $message = $result['message'];        $message = $result['message'];

    

    // 处理多文件上传        $messageType = $result['type'];        $messageType = $result['type'];

    if (is_array($files['name'])) {

        for ($i = 0; $i < count($files['name']); $i++) {    }    }

            if ($files['error'][$i] === UPLOAD_ERR_OK) {

                $originalName = pathinfo($files['name'][$i], PATHINFO_FILENAME);}}

                $extension = pathinfo($files['name'][$i], PATHINFO_EXTENSION);

                

                if ($isIcon) {

                    $filename = $originalName . '-' . $iconType . '.' . $extension;// 获取所有漫画数据// 获取所有漫画数据

                } else {

                    $filename = uniqid() . '_' . $files['name'][$i];$allComics = get_all_comics_data();$allComics = get_all_comics_data();

                }

                

                $targetPath = $uploadDir . $filename;

                // 处理图片上传的函数 - 修正版本// 处理图片上传的函数

                if (move_uploaded_file($files['tmp_name'][$i], $targetPath)) {

                    $uploadedFiles[] = $webPath . $filename;function handleImageUpload($fileKey, $folder = 'comic', $isIcon = false, $iconType = '') {function handleImageUpload($fileKey, $folder = 'comic', $isIcon = false, $iconType = '') {

                }

            }    $uploadedFiles = [];    $uploadedFiles = [];

        }

    } else {        

        // 单文件上传

        if ($files['error'] === UPLOAD_ERR_OK) {    if (!isset($_FILES[$fileKey])) {    if (!isset($_FILES[$fileKey])) {

            $originalName = pathinfo($files['name'], PATHINFO_FILENAME);

            $extension = pathinfo($files['name'], PATHINFO_EXTENSION);        return $uploadedFiles;        return $uploadedFiles;

            

            if ($isIcon) {    }    }

                $filename = $originalName . '-' . $iconType . '.' . $extension;

            } else {        

                $filename = uniqid() . '_' . $files['name'];

            }    $files = $_FILES[$fileKey];    $files = $_FILES[$fileKey];

            

            $targetPath = $uploadDir . $filename;        

            

            if (move_uploaded_file($files['tmp_name'], $targetPath)) {    // 根据类型设置上传目录    // 根据类型设置上传目录

                $uploadedFiles[] = $webPath . $filename;

            }    if ($isIcon) {    if ($isIcon) {

        }

    }        $uploadDir = __DIR__ . '/../../../assets/images/' . $folder . '/thumbs/';        $uploadDir = __DIR__ . '/../../../assets/images/' . $folder . '/thumbs/';

    

    return $uploadedFiles;        $webPath = '/assets/images/' . $folder . '/thumbs/';    } else {

}

    } else {        $uploadDir = __DIR__ . '/../../../assets/images/' . $folder . '/';

// 添加漫画处理

function handleComicAdd() {        $uploadDir = __DIR__ . '/../../../assets/images/' . $folder . '/';    }

    $comicData = [

        'title' => $_POST['title'] ?? '',        $webPath = '/assets/images/' . $folder . '/';    

        'subtitle' => $_POST['subtitle'] ?? '',

        'lines' => $_POST['lines'] ?? '',    }    if (!is_dir($uploadDir)) {

        'alt' => $_POST['alt'] ?? $_POST['title'],

        'status' => $_POST['status'] ?? 'active'            mkdir($uploadDir, 0755, true);

    ];

        if (!is_dir($uploadDir)) {    }

    // 处理主要图片上传

    $mainImages = handleImageUpload('images', 'comic', false);        mkdir($uploadDir, 0755, true);    

    if ($mainImages) {

        $comicData['images'] = $mainImages;    }    // 处理多文件上传

    }

            if (is_array($files['name'])) {

    // 处理图标上传

    $iconDefault = handleImageUpload('icon_default', 'comic', true, 'icon01');    // 处理多文件上传        for ($i = 0; $i < count($files['name']); $i++) {

    if ($iconDefault) {

        $comicData['icon_default'] = $iconDefault[0];    if (is_array($files['name'])) {            if ($files['error'][$i] === UPLOAD_ERR_OK) {

    }

            for ($i = 0; $i < count($files['name']); $i++) {                $originalName = pathinfo($files['name'][$i], PATHINFO_FILENAME);

    $iconHover = handleImageUpload('icon_hover', 'comic', true, 'icon02');

    if ($iconHover) {            if ($files['error'][$i] === UPLOAD_ERR_OK) {                $extension = pathinfo($files['name'][$i], PATHINFO_EXTENSION);

        $comicData['icon_hover'] = $iconHover[0];

    }                $originalName = pathinfo($files['name'][$i], PATHINFO_FILENAME);                

    

    $comicId = $_POST['comic_id'] ?: uniqid('comic_');                $extension = pathinfo($files['name'][$i], PATHINFO_EXTENSION);                if ($isIcon) {

    $comicData['id'] = $comicId;

                                        $filename = $originalName . '-' . $iconType . '.' . $extension;

    if (add_comic($comicData)) {

        return ['message' => '漫画添加成功！', 'type' => 'success'];                if ($isIcon) {                    $relativePath = '/assets/images/' . $folder . '/thumbs/' . $filename;

    } else {

        return ['message' => '漫画添加失败！', 'type' => 'error'];                    $filename = $originalName . '-' . $iconType . '.' . $extension;                } else {

    }

}                } else {                    $filename = uniqid() . '_' . $files['name'][$i];



// 编辑漫画处理                    $filename = uniqid() . '_' . $files['name'][$i];                    $relativePath = '/assets/images/' . $folder . '/' . $filename;

function handleComicEdit() {

    $comicId = $_POST['edit_id'];                }                }

    $comicData = [

        'title' => $_POST['title'] ?? '',                                

        'subtitle' => $_POST['subtitle'] ?? '',

        'lines' => $_POST['lines'] ?? '',                $targetPath = $uploadDir . $filename;                $targetPath = $uploadDir . $filename;

        'alt' => $_POST['alt'] ?? $_POST['title'],

        'status' => $_POST['status'] ?? 'active'                                

    ];

                    if (move_uploaded_file($files['tmp_name'][$i], $targetPath)) {                if (move_uploaded_file($files['tmp_name'][$i], $targetPath)) {

    // 处理图片上传（如果有新上传的话）

    $mainImages = handleImageUpload('images', 'comic', false);                    $uploadedFiles[] = $webPath . $filename;                    $uploadedFiles[] = $relativePath;

    if ($mainImages) {

        $comicData['images'] = $mainImages;                }                }

    }

                }            }

    $iconDefault = handleImageUpload('icon_default', 'comic', true, 'icon01');

    if ($iconDefault) {        }        }

        $comicData['icon_default'] = $iconDefault[0];

    }    } else {    } else {

    

    $iconHover = handleImageUpload('icon_hover', 'comic', true, 'icon02');        // 单文件上传        // 单文件上传

    if ($iconHover) {

        $comicData['icon_hover'] = $iconHover[0];        if ($files['error'] === UPLOAD_ERR_OK) {        if ($files['error'] === UPLOAD_ERR_OK) {

    }

                $originalName = pathinfo($files['name'], PATHINFO_FILENAME);            $originalName = pathinfo($files['name'], PATHINFO_FILENAME);

    if (update_comic($comicId, $comicData)) {

        return ['message' => '漫画更新成功！', 'type' => 'success'];            $extension = pathinfo($files['name'], PATHINFO_EXTENSION);            $extension = pathinfo($files['name'], PATHINFO_EXTENSION);

    } else {

        return ['message' => '漫画更新失败！', 'type' => 'error'];                        

    }

}            if ($isIcon) {            if ($isIcon) {



// 删除漫画处理                $filename = $originalName . '-' . $iconType . '.' . $extension;                $filename = $originalName . '-' . $iconType . '.' . $extension;

function handleComicDelete() {

    $comicId = $_POST['delete_id'];            } else {                $relativePath = '/assets/images/' . $folder . '/thumbs/' . $filename;

    

    if (delete_comic($comicId)) {                $filename = uniqid() . '_' . $files['name'];            } else {

        return ['message' => '漫画删除成功！', 'type' => 'success'];

    } else {            }                $filename = uniqid() . '_' . $files['name'];

        return ['message' => '漫画删除失败！', 'type' => 'error'];

    }                            $relativePath = '/assets/images/' . $folder . '/' . $filename;

}

?>            $targetPath = $uploadDir . $filename;            }



<style>                        

.comic-manager {

    padding: 20px;            if (move_uploaded_file($files['tmp_name'], $targetPath)) {            $targetPath = $uploadDir . $filename;

    max-width: 1200px;

    margin: 0 auto;                $uploadedFiles[] = $webPath . $filename;            

}

            }            if (move_uploaded_file($files['tmp_name'], $targetPath)) {

.comic-form {

    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);        }                $uploadedFiles[] = $relativePath;

    border-radius: 15px;

    padding: 30px;    }            }

    margin-bottom: 30px;

    box-shadow: 0 10px 30px rgba(0,0,0,0.2);            }

    color: white;

}    return $uploadedFiles;    }



.form-group {}    

    margin-bottom: 20px;

}    return $uploadedFiles;



.form-group label {// 添加漫画处理}

    display: block;

    margin-bottom: 8px;function handleComicAdd() {

    font-weight: bold;

    color: #fff;    $comicData = [// 添加漫画处理

}

        'title' => $_POST['title'] ?? '',function handleComicAdd() {

.form-control {

    width: 100%;        'subtitle' => $_POST['subtitle'] ?? '',    $comicData = [

    padding: 12px 15px;

    border: none;        'lines' => $_POST['lines'] ?? '',        'title' => $_POST['title'] ?? '',

    border-radius: 8px;

    background: rgba(255,255,255,0.1);        'alt' => $_POST['alt'] ?? $_POST['title'],        'subtitle' => $_POST['subtitle'] ?? '',

    color: white;

    backdrop-filter: blur(10px);        'status' => $_POST['status'] ?? 'active'        'lines' => $_POST['lines'] ?? '',

    transition: all 0.3s ease;

}    ];        'alt' => $_POST['alt'] ?? $_POST['title'],



.form-control:focus {            'status' => $_POST['status'] ?? 'active'

    outline: none;

    background: rgba(255,255,255,0.2);    // 处理主要图片上传    ];

    box-shadow: 0 0 0 3px rgba(255,255,255,0.1);

}    $mainImages = handleImageUpload('images', 'comic', false);    



.form-control::placeholder {    if ($mainImages) {    // 处理主要图片上传

    color: rgba(255,255,255,0.6);

}        $comicData['images'] = $mainImages;    $mainImages = handleImageUpload('images');



.btn {    }    if ($mainImages) {

    padding: 12px 25px;

    border: none;            $comicData['images'] = $mainImages;

    border-radius: 8px;

    cursor: pointer;    // 处理图标上传 - 使用新的命名规则    }

    font-weight: bold;

    text-transform: uppercase;    $iconDefault = handleImageUpload('icon_default', 'comic', true, 'icon01');    

    letter-spacing: 1px;

    transition: all 0.3s ease;    if ($iconDefault) {    // 处理图标上传

    margin: 0 5px;

}        $comicData['icon_default'] = $iconDefault[0];    $iconDefault = handleImageUpload('icon_default', 'comic', true, 'icon01');



.btn-primary {    }    if ($iconDefault) {

    background: linear-gradient(45deg, #ff6b6b, #ee5a24);

    color: white;            $comicData['icon_default'] = $iconDefault[0];

}

    $iconHover = handleImageUpload('icon_hover', 'comic', true, 'icon02');    }

.btn-success {

    background: linear-gradient(45deg, #00d2d3, #54a0ff);    if ($iconHover) {    

    color: white;

}        $comicData['icon_hover'] = $iconHover[0];    $iconHover = handleImageUpload('icon_hover', 'comic', true, 'icon02');



.btn-danger {    }    if ($iconHover) {

    background: linear-gradient(45deg, #ff3838, #ff6348);

    color: white;            $comicData['icon_hover'] = $iconHover[0];

}

    $comicId = $_POST['comic_id'] ?: uniqid('comic_');    }

.btn:hover {

    transform: translateY(-2px);    $comicData['id'] = $comicId;    

    box-shadow: 0 5px 15px rgba(0,0,0,0.3);

}        $comicId = $_POST['comic_id'] ?: uniqid('comic_');



.comics-list {    if (add_comic($comicData)) {    $comicData['id'] = $comicId;

    background: white;

    border-radius: 15px;        return ['message' => '漫画添加成功！', 'type' => 'success'];    

    padding: 20px;

    box-shadow: 0 5px 20px rgba(0,0,0,0.1);    } else {    if (add_comic($comicData)) {

}

        return ['message' => '漫画添加失败！', 'type' => 'error'];        return ['message' => '漫画添加成功！', 'type' => 'success'];

.comic-item {

    display: flex;    }    } else {

    align-items: center;

    padding: 15px;}        return ['message' => '漫画添加失败！', 'type' => 'error'];

    border-bottom: 1px solid #eee;

    transition: all 0.3s ease;    }

}

// 编辑漫画处理}

.comic-item:hover {

    background: #f8f9fa;function handleComicEdit() {

    transform: translateX(5px);

}    $comicId = $_POST['edit_id'];// 编辑漫画处理



.comic-preview {    $comicData = [function handleComicEdit() {

    width: 80px;

    height: 80px;        'title' => $_POST['title'] ?? '',    $comicId = $_POST['edit_id'];

    object-fit: cover;

    border-radius: 10px;        'subtitle' => $_POST['subtitle'] ?? '',    $comicData = [

    margin-right: 20px;

}        'lines' => $_POST['lines'] ?? '',        'title' => $_POST['title'] ?? '',



.comic-info {        'alt' => $_POST['alt'] ?? $_POST['title'],        'subtitle' => $_POST['subtitle'] ?? '',

    flex: 1;

}        'status' => $_POST['status'] ?? 'active'        'lines' => $_POST['lines'] ?? '',



.comic-actions {    ];        'alt' => $_POST['alt'] ?? $_POST['title'],

    display: flex;

    gap: 10px;            'status' => $_POST['status'] ?? 'active'

}

    // 处理图片上传（如果有新上传的话）    ];

.alert {

    padding: 15px;    $mainImages = handleImageUpload('images', 'comic', false);    

    border-radius: 8px;

    margin-bottom: 20px;    if ($mainImages) {    // 处理图片上传（如果有新上传的话）

    font-weight: bold;

}        $comicData['images'] = $mainImages;    $mainImages = handleImageUpload('images');



.alert-success {    }    if ($mainImages) {

    background: #d4edda;

    color: #155724;            $comicData['images'] = $mainImages;

    border: 1px solid #c3e6cb;

}    $iconDefault = handleImageUpload('icon_default', 'comic', true, 'icon01');    }



.alert-error {    if ($iconDefault) {    

    background: #f8d7da;

    color: #721c24;        $comicData['icon_default'] = $iconDefault[0];    $iconDefault = handleImageUpload('icon_default');

    border: 1px solid #f5c6cb;

}    }    if ($iconDefault) {



.file-upload-area {            $comicData['icon_default'] = $iconDefault[0];

    border: 2px dashed rgba(255,255,255,0.3);

    border-radius: 10px;    $iconHover = handleImageUpload('icon_hover', 'comic', true, 'icon02');    }

    padding: 20px;

    text-align: center;    if ($iconHover) {    

    transition: all 0.3s ease;

}        $comicData['icon_hover'] = $iconHover[0];    $iconHover = handleImageUpload('icon_hover');



.file-upload-area:hover {    }    if ($iconHover) {

    border-color: rgba(255,255,255,0.6);

    background: rgba(255,255,255,0.05);            $comicData['icon_hover'] = $iconHover[0];

}

    if (update_comic($comicId, $comicData)) {    }

.row {

    display: flex;        return ['message' => '漫画更新成功！', 'type' => 'success'];    

    gap: 20px;

    margin: -10px;    } else {    if (update_comic($comicId, $comicData)) {

}

        return ['message' => '漫画更新失败！', 'type' => 'error'];        return ['message' => '漫画更新成功！', 'type' => 'success'];

.col-md-6 {

    flex: 1;    }    } else {

    padding: 10px;

}}        return ['message' => '漫画更新失败！', 'type' => 'error'];



.path-info {    }

    font-size: 11px;

    color: rgba(255,255,255,0.7);// 删除漫画处理}

    margin-top: 5px;

    font-style: italic;function handleComicDelete() {

}

</style>    $comicId = $_POST['delete_id'];// 删除漫画处理



<div class="comic-manager">    function handleComicDelete() {

    <h1><i class="bi bi-palette"></i> Comic Manager - 漫画管理</h1>

        if (delete_comic($comicId)) {    $comicId = $_POST['delete_id'];

    <?php if ($message): ?>

        <div class="alert alert-<?= $messageType ?>">        return ['message' => '漫画删除成功！', 'type' => 'success'];    

            <?= htmlspecialchars($message) ?>

        </div>    } else {    if (delete_comic($comicId)) {

    <?php endif; ?>

            return ['message' => '漫画删除失败！', 'type' => 'error'];        return ['message' => '漫画删除成功！', 'type' => 'success'];

    <!-- 添加/编辑表单 -->

    <div class="comic-form">    }    } else {

        <h2><i class="bi bi-plus-circle"></i> 添加新漫画</h2>

        <form method="post" enctype="multipart/form-data" id="comicForm">}        return ['message' => '漫画删除失败！', 'type' => 'error'];

            <input type="hidden" name="action" value="add" id="formAction">

            <input type="hidden" name="edit_id" id="editId">?>    }

            

            <div class="row">}

                <div class="col-md-6">

                    <div class="form-group"><style>?>

                        <label for="comic_id">漫画ID (唯一标识)</label>

                        <input type="text" name="comic_id" id="comic_id" class="form-control" .comic-manager {

                               placeholder="留空自动生成">

                    </div>    padding: 20px;<style>

                    

                    <div class="form-group">    max-width: 1200px;.comic-manager {

                        <label for="title">标题 *</label>

                        <input type="text" name="title" id="title" class="form-control"     margin: 0 auto;    padding: 20px;

                               placeholder="输入漫画标题" required>

                    </div>}    max-width: 1200px;

                    

                    <div class="form-group">    margin: 0 auto;

                        <label for="subtitle">副标题</label>

                        <input type="text" name="subtitle" id="subtitle" class="form-control" .comic-form {}

                               placeholder="输入副标题">

                    </div>    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);

                    

                    <div class="form-group">    border-radius: 15px;.comic-form {

                        <label for="lines">描述文字</label>

                        <textarea name="lines" id="lines" class="form-control" rows="3"     padding: 30px;    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);

                                  placeholder="输入描述文字，支持HTML标签"></textarea>

                    </div>    margin-bottom: 30px;    border-radius: 15px;

                    

                    <div class="form-group">    box-shadow: 0 10px 30px rgba(0,0,0,0.2);    padding: 30px;

                        <label for="alt">Alt文本</label>

                        <input type="text" name="alt" id="alt" class="form-control"     color: white;    margin-bottom: 30px;

                               placeholder="图片替代文本">

                    </div>}    box-shadow: 0 10px 30px rgba(0,0,0,0.2);

                    

                    <div class="form-group">    color: white;

                        <label for="status">状态</label>

                        <select name="status" id="status" class="form-control">.form-group {}

                            <option value="active">激活</option>

                            <option value="inactive">禁用</option>    margin-bottom: 20px;

                        </select>

                    </div>}.form-group {

                </div>

                    margin-bottom: 20px;

                <div class="col-md-6">

                    <div class="form-group">.form-group label {}

                        <label for="images">主要图片 (支持多张)</label>

                        <div class="file-upload-area">    display: block;

                            <input type="file" name="images[]" id="images" class="form-control" 

                                   multiple accept="image/*">    margin-bottom: 8px;.form-group label {

                            <p style="margin-top: 10px; opacity: 0.7;">

                                <i class="bi bi-cloud-upload"></i>     font-weight: bold;    display: block;

                                点击选择或拖拽图片文件

                            </p>    color: #fff;    margin-bottom: 8px;

                            <div class="path-info">储存位置: /assets/images/comic/</div>

                        </div>}    font-weight: bold;

                    </div>

                        color: #fff;

                    <div class="form-group">

                        <label for="icon_default">默认图标</label>.form-control {}

                        <div class="file-upload-area">

                            <input type="file" name="icon_default" id="icon_default"     width: 100%;

                                   class="form-control" accept="image/*">

                            <div class="path-info">储存为: 图片名-icon01.jpg</div>    padding: 12px 15px;.form-control {

                            <div class="path-info">位置: /assets/images/comic/thumbs/</div>

                        </div>    border: none;    width: 100%;

                    </div>

                        border-radius: 8px;    padding: 12px 15px;

                    <div class="form-group">

                        <label for="icon_hover">悬停图标</label>    background: rgba(255,255,255,0.1);    border: none;

                        <div class="file-upload-area">

                            <input type="file" name="icon_hover" id="icon_hover"     color: white;    border-radius: 8px;

                                   class="form-control" accept="image/*">

                            <div class="path-info">储存为: 图片名-icon02.jpg</div>    backdrop-filter: blur(10px);    background: rgba(255,255,255,0.1);

                            <div class="path-info">位置: /assets/images/comic/thumbs/</div>

                        </div>    transition: all 0.3s ease;    color: white;

                    </div>

                </div>}    backdrop-filter: blur(10px);

            </div>

                transition: all 0.3s ease;

            <div style="text-align: center; margin-top: 20px;">

                <button type="submit" class="btn btn-primary">.form-control:focus {}

                    <i class="bi bi-save"></i> 保存漫画

                </button>    outline: none;

                <button type="button" class="btn btn-success" onclick="resetForm()">

                    <i class="bi bi-arrow-clockwise"></i> 重置表单    background: rgba(255,255,255,0.2);.form-control:focus {

                </button>

            </div>    box-shadow: 0 0 0 3px rgba(255,255,255,0.1);    outline: none;

        </form>

    </div>}    background: rgba(255,255,255,0.2);

    

    <!-- 漫画列表 -->    box-shadow: 0 0 0 3px rgba(255,255,255,0.1);

    <div class="comics-list">

        <h2><i class="bi bi-collection"></i> 现有漫画列表</h2>.form-control::placeholder {}

        

        <?php if (empty($allComics)): ?>    color: rgba(255,255,255,0.6);

            <p style="text-align: center; color: #666; padding: 40px;">

                <i class="bi bi-inbox" style="font-size: 48px; display: block; margin-bottom: 20px;"></i>}.form-control::placeholder {

                暂无漫画内容

            </p>    color: rgba(255,255,255,0.6);

        <?php else: ?>

            <?php foreach ($allComics as $comicId => $comic): ?>.btn {}

                <div class="comic-item">

                    <?php if (!empty($comic['icon_default'])): ?>    padding: 12px 25px;

                        <img src="<?= htmlspecialchars($comic['icon_default']) ?>" 

                             alt="<?= htmlspecialchars($comic['title']) ?>" class="comic-preview">    border: none;.btn {

                    <?php else: ?>

                        <div class="comic-preview" style="background: #ddd; display: flex; align-items: center; justify-content: center;">    border-radius: 8px;    padding: 12px 25px;

                            <i class="bi bi-image" style="font-size: 24px;"></i>

                        </div>    cursor: pointer;    border: none;

                    <?php endif; ?>

                        font-weight: bold;    border-radius: 8px;

                    <div class="comic-info">

                        <h4><?= htmlspecialchars($comic['title']) ?></h4>    text-transform: uppercase;    cursor: pointer;

                        <p><strong>ID:</strong> <?= htmlspecialchars($comicId) ?></p>

                        <p><strong>副标题:</strong> <?= htmlspecialchars($comic['subtitle']) ?></p>    letter-spacing: 1px;    font-weight: bold;

                        <p><strong>状态:</strong> 

                            <span style="color: <?= $comic['status'] === 'active' ? 'green' : 'red' ?>">    transition: all 0.3s ease;    text-transform: uppercase;

                                <?= $comic['status'] === 'active' ? '激活' : '禁用' ?>

                            </span>    margin: 0 5px;    letter-spacing: 1px;

                        </p>

                        <?php if (!empty($comic['images'])): ?>}    transition: all 0.3s ease;

                            <p><strong>图片:</strong> <?= count($comic['images']) ?> 张</p>

                        <?php endif; ?>    margin: 0 5px;

                        <?php if (!empty($comic['created_at'])): ?>

                            <p><strong>创建:</strong> <?= htmlspecialchars($comic['created_at']) ?></p>.btn-primary {}

                        <?php endif; ?>

                    </div>    background: linear-gradient(45deg, #ff6b6b, #ee5a24);

                    

                    <div class="comic-actions">    color: white;.btn-primary {

                        <button class="btn btn-success" onclick="editComic('<?= htmlspecialchars($comicId) ?>')">

                            <i class="bi bi-pencil"></i> 编辑}    background: linear-gradient(45deg, #ff6b6b, #ee5a24);

                        </button>

                        <form method="post" style="display: inline;"     color: white;

                              onsubmit="return confirm('确定要删除这个漫画吗？')">

                            <input type="hidden" name="action" value="delete">.btn-success {}

                            <input type="hidden" name="delete_id" value="<?= htmlspecialchars($comicId) ?>">

                            <button type="submit" class="btn btn-danger">    background: linear-gradient(45deg, #00d2d3, #54a0ff);

                                <i class="bi bi-trash"></i> 删除

                            </button>    color: white;.btn-success {

                        </form>

                    </div>}    background: linear-gradient(45deg, #00d2d3, #54a0ff);

                </div>

            <?php endforeach; ?>    color: white;

        <?php endif; ?>

    </div>.btn-danger {}

</div>

    background: linear-gradient(45deg, #ff3838, #ff6348);

<script>

// 存储漫画数据用于编辑    color: white;.btn-danger {

const comicsData = <?= json_encode($allComics) ?>;

}    background: linear-gradient(45deg, #ff3838, #ff6348);

function resetForm() {

    document.getElementById('comicForm').reset();    color: white;

    document.getElementById('formAction').value = 'add';

    document.getElementById('editId').value = '';.btn:hover {}

    document.querySelector('.comic-form h2').innerHTML = '<i class="bi bi-plus-circle"></i> 添加新漫画';

}    transform: translateY(-2px);



function editComic(comicId) {    box-shadow: 0 5px 15px rgba(0,0,0,0.3);.btn:hover {

    const comic = comicsData[comicId];

    if (!comic) return;}    transform: translateY(-2px);

    

    // 设置表单为编辑模式    box-shadow: 0 5px 15px rgba(0,0,0,0.3);

    document.getElementById('formAction').value = 'edit';

    document.getElementById('editId').value = comicId;.comics-list {}

    document.querySelector('.comic-form h2').innerHTML = '<i class="bi bi-pencil"></i> 编辑漫画: ' + comic.title;

        background: white;

    // 填充表单数据

    document.getElementById('comic_id').value = comicId;    border-radius: 15px;.comics-list {

    document.getElementById('title').value = comic.title || '';

    document.getElementById('subtitle').value = comic.subtitle || '';    padding: 20px;    background: white;

    document.getElementById('lines').value = comic.lines || '';

    document.getElementById('alt').value = comic.alt || '';    box-shadow: 0 5px 20px rgba(0,0,0,0.1);    border-radius: 15px;

    document.getElementById('status').value = comic.status || 'active';

    }    padding: 20px;

    // 滚动到表单顶部

    document.querySelector('.comic-form').scrollIntoView({ behavior: 'smooth' });    box-shadow: 0 5px 20px rgba(0,0,0,0.1);

}

.comic-item {}

// 文件上传预览功能

document.querySelectorAll('input[type="file"]').forEach(input => {    display: flex;

    input.addEventListener('change', function(e) {

        const files = e.target.files;    align-items: center;.comic-item {

        if (files.length > 0) {

            console.log(`选择了 ${files.length} 个文件:`, Array.from(files).map(f => f.name));    padding: 15px;    display: flex;

            

            // 显示文件信息    border-bottom: 1px solid #eee;    align-items: center;

            const parent = this.closest('.file-upload-area');

            let infoDiv = parent.querySelector('.file-info');    transition: all 0.3s ease;    padding: 15px;

            if (!infoDiv) {

                infoDiv = document.createElement('div');}    border-bottom: 1px solid #eee;

                infoDiv.className = 'file-info';

                infoDiv.style.cssText = 'margin-top: 10px; font-size: 12px; color: rgba(255,255,255,0.8);';    transition: all 0.3s ease;

                parent.appendChild(infoDiv);

            }.comic-item:hover {}

            

            if (files.length === 1) {    background: #f8f9fa;

                infoDiv.textContent = `已选择: ${files[0].name}`;

            } else {    transform: translateX(5px);.comic-item:hover {

                infoDiv.textContent = `已选择 ${files.length} 个文件`;

            }}    background: #f8f9fa;

        }

    });    transform: translateX(5px);

});

</script>.comic-preview {}

    width: 80px;

    height: 80px;.comic-preview {

    object-fit: cover;    width: 80px;

    border-radius: 10px;    height: 80px;

    margin-right: 20px;    object-fit: cover;

}    border-radius: 10px;

    margin-right: 20px;

.comic-info {}

    flex: 1;

}.comic-info {

    flex: 1;

.comic-actions {}

    display: flex;

    gap: 10px;.comic-actions {

}    display: flex;

    gap: 10px;

.alert {}

    padding: 15px;

    border-radius: 8px;.alert {

    margin-bottom: 20px;    padding: 15px;

    font-weight: bold;    border-radius: 8px;

}    margin-bottom: 20px;

    font-weight: bold;

.alert-success {}

    background: #d4edda;

    color: #155724;.alert-success {

    border: 1px solid #c3e6cb;    background: #d4edda;

}    color: #155724;

    border: 1px solid #c3e6cb;

.alert-error {}

    background: #f8d7da;

    color: #721c24;.alert-error {

    border: 1px solid #f5c6cb;    background: #f8d7da;

}    color: #721c24;

    border: 1px solid #f5c6cb;

.file-upload-area {}

    border: 2px dashed rgba(255,255,255,0.3);

    border-radius: 10px;.file-upload-area {

    padding: 20px;    border: 2px dashed rgba(255,255,255,0.3);

    text-align: center;    border-radius: 10px;

    transition: all 0.3s ease;    padding: 20px;

}    text-align: center;

    transition: all 0.3s ease;

.file-upload-area:hover {}

    border-color: rgba(255,255,255,0.6);

    background: rgba(255,255,255,0.05);.file-upload-area:hover {

}    border-color: rgba(255,255,255,0.6);

    background: rgba(255,255,255,0.05);

.row {}

    display: flex;

    gap: 20px;.row {

    margin: -10px;    display: flex;

}    gap: 20px;

    margin: -10px;

.col-md-6 {}

    flex: 1;

    padding: 10px;.col-md-6 {

}    flex: 1;

    padding: 10px;

.path-info {}

    font-size: 11px;</style>

    color: rgba(255,255,255,0.7);

    margin-top: 5px;<div class="comic-manager">

    font-style: italic;    <h1><i class="bi bi-palette"></i> Comic Manager - 漫画管理</h1>

}    

</style>    <?php if ($message): ?>

        <div class="alert alert-<?= $messageType ?>">

<div class="comic-manager">            <?= htmlspecialchars($message) ?>

    <h1><i class="bi bi-palette"></i> Comic Manager - 漫画管理</h1>        </div>

        <?php endif; ?>

    <?php if ($message): ?>    

        <div class="alert alert-<?= $messageType ?>">    <!-- 添加/编辑表单 -->

            <?= htmlspecialchars($message) ?>    <div class="comic-form">

        </div>        <h2><i class="bi bi-plus-circle"></i> 添加新漫画</h2>

    <?php endif; ?>        <form method="post" enctype="multipart/form-data" id="comicForm">

                <input type="hidden" name="action" value="add" id="formAction">

    <!-- 添加/编辑表单 -->            <input type="hidden" name="edit_id" id="editId">

    <div class="comic-form">            

        <h2><i class="bi bi-plus-circle"></i> 添加新漫画</h2>            <div class="row">

        <form method="post" enctype="multipart/form-data" id="comicForm">                <div class="col-md-6">

            <input type="hidden" name="action" value="add" id="formAction">                    <div class="form-group">

            <input type="hidden" name="edit_id" id="editId">                        <label for="comic_id">漫画ID (唯一标识)</label>

                                    <input type="text" name="comic_id" id="comic_id" class="form-control" 

            <div class="row">                               placeholder="留空自动生成">

                <div class="col-md-6">                    </div>

                    <div class="form-group">                    

                        <label for="comic_id">漫画ID (唯一标识)</label>                    <div class="form-group">

                        <input type="text" name="comic_id" id="comic_id" class="form-control"                         <label for="title">标题 *</label>

                               placeholder="留空自动生成">                        <input type="text" name="title" id="title" class="form-control" 

                    </div>                               placeholder="输入漫画标题" required>

                                        </div>

                    <div class="form-group">                    

                        <label for="title">标题 *</label>                    <div class="form-group">

                        <input type="text" name="title" id="title" class="form-control"                         <label for="subtitle">副标题</label>

                               placeholder="输入漫画标题" required>                        <input type="text" name="subtitle" id="subtitle" class="form-control" 

                    </div>                               placeholder="输入副标题">

                                        </div>

                    <div class="form-group">                    

                        <label for="subtitle">副标题</label>                    <div class="form-group">

                        <input type="text" name="subtitle" id="subtitle" class="form-control"                         <label for="lines">描述文字</label>

                               placeholder="输入副标题">                        <textarea name="lines" id="lines" class="form-control" rows="3" 

                    </div>                                  placeholder="输入描述文字，支持HTML标签"></textarea>

                                        </div>

                    <div class="form-group">                    

                        <label for="lines">描述文字</label>                    <div class="form-group">

                        <textarea name="lines" id="lines" class="form-control" rows="3"                         <label for="alt">Alt文本</label>

                                  placeholder="输入描述文字，支持HTML标签"></textarea>                        <input type="text" name="alt" id="alt" class="form-control" 

                    </div>                               placeholder="图片替代文本">

                                        </div>

                    <div class="form-group">                    

                        <label for="alt">Alt文本</label>                    <div class="form-group">

                        <input type="text" name="alt" id="alt" class="form-control"                         <label for="status">状态</label>

                               placeholder="图片替代文本">                        <select name="status" id="status" class="form-control">

                    </div>                            <option value="active">激活</option>

                                                <option value="inactive">禁用</option>

                    <div class="form-group">                        </select>

                        <label for="status">状态</label>                    </div>

                        <select name="status" id="status" class="form-control">                </div>

                            <option value="active">激活</option>                

                            <option value="inactive">禁用</option>                <div class="col-md-6">

                        </select>                    <div class="form-group">

                    </div>                        <label for="images">主要图片 (支持多张)</label>

                </div>                        <div class="file-upload-area">

                                            <input type="file" name="images[]" id="images" class="form-control" 

                <div class="col-md-6">                                   multiple accept="image/*">

                    <div class="form-group">                            <p style="margin-top: 10px; opacity: 0.7;">

                        <label for="images">主要图片 (支持多张)</label>                                <i class="bi bi-cloud-upload"></i> 

                        <div class="file-upload-area">                                点击选择或拖拽图片文件

                            <input type="file" name="images[]" id="images" class="form-control"                             </p>

                                   multiple accept="image/*">                        </div>

                            <p style="margin-top: 10px; opacity: 0.7;">                    </div>

                                <i class="bi bi-cloud-upload"></i>                     

                                点击选择或拖拽图片文件                    <div class="form-group">

                            </p>                        <label for="icon_default">默认图标</label>

                            <div class="path-info">储存位置: /assets/images/comic/</div>                        <div class="file-upload-area">

                        </div>                            <input type="file" name="icon_default" id="icon_default" 

                    </div>                                   class="form-control" accept="image/*">

                                            </div>

                    <div class="form-group">                    </div>

                        <label for="icon_default">默认图标</label>                    

                        <div class="file-upload-area">                    <div class="form-group">

                            <input type="file" name="icon_default" id="icon_default"                         <label for="icon_hover">悬停图标</label>

                                   class="form-control" accept="image/*">                        <div class="file-upload-area">

                            <div class="path-info">储存为: 图片名-icon01.jpg</div>                            <input type="file" name="icon_hover" id="icon_hover" 

                            <div class="path-info">位置: /assets/images/comic/thumbs/</div>                                   class="form-control" accept="image/*">

                        </div>                        </div>

                    </div>                    </div>

                                    </div>

                    <div class="form-group">            </div>

                        <label for="icon_hover">悬停图标</label>            

                        <div class="file-upload-area">            <div style="text-align: center; margin-top: 20px;">

                            <input type="file" name="icon_hover" id="icon_hover"                 <button type="submit" class="btn btn-primary">

                                   class="form-control" accept="image/*">                    <i class="bi bi-save"></i> 保存漫画

                            <div class="path-info">储存为: 图片名-icon02.jpg</div>                </button>

                            <div class="path-info">位置: /assets/images/comic/thumbs/</div>                <button type="button" class="btn btn-success" onclick="resetForm()">

                        </div>                    <i class="bi bi-arrow-clockwise"></i> 重置表单

                    </div>                </button>

                </div>            </div>

            </div>        </form>

                </div>

            <div style="text-align: center; margin-top: 20px;">    

                <button type="submit" class="btn btn-primary">    <!-- 漫画列表 -->

                    <i class="bi bi-save"></i> 保存漫画    <div class="comics-list">

                </button>        <h2><i class="bi bi-collection"></i> 现有漫画列表</h2>

                <button type="button" class="btn btn-success" onclick="resetForm()">        

                    <i class="bi bi-arrow-clockwise"></i> 重置表单        <?php if (empty($allComics)): ?>

                </button>            <p style="text-align: center; color: #666; padding: 40px;">

            </div>                <i class="bi bi-inbox" style="font-size: 48px; display: block; margin-bottom: 20px;"></i>

        </form>                暂无漫画内容

    </div>            </p>

            <?php else: ?>

    <!-- 漫画列表 -->            <?php foreach ($allComics as $comicId => $comic): ?>

    <div class="comics-list">                <div class="comic-item">

        <h2><i class="bi bi-collection"></i> 现有漫画列表</h2>                    <?php if (!empty($comic['icon_default'])): ?>

                                <img src="<?= htmlspecialchars($comic['icon_default']) ?>" 

        <?php if (empty($allComics)): ?>                             alt="<?= htmlspecialchars($comic['title']) ?>" class="comic-preview">

            <p style="text-align: center; color: #666; padding: 40px;">                    <?php else: ?>

                <i class="bi bi-inbox" style="font-size: 48px; display: block; margin-bottom: 20px;"></i>                        <div class="comic-preview" style="background: #ddd; display: flex; align-items: center; justify-content: center;">

                暂无漫画内容                            <i class="bi bi-image" style="font-size: 24px;"></i>

            </p>                        </div>

        <?php else: ?>                    <?php endif; ?>

            <?php foreach ($allComics as $comicId => $comic): ?>                    

                <div class="comic-item">                    <div class="comic-info">

                    <?php if (!empty($comic['icon_default'])): ?>                        <h4><?= htmlspecialchars($comic['title']) ?></h4>

                        <img src="<?= htmlspecialchars($comic['icon_default']) ?>"                         <p><strong>ID:</strong> <?= htmlspecialchars($comicId) ?></p>

                             alt="<?= htmlspecialchars($comic['title']) ?>" class="comic-preview">                        <p><strong>副标题:</strong> <?= htmlspecialchars($comic['subtitle']) ?></p>

                    <?php else: ?>                        <p><strong>状态:</strong> 

                        <div class="comic-preview" style="background: #ddd; display: flex; align-items: center; justify-content: center;">                            <span style="color: <?= $comic['status'] === 'active' ? 'green' : 'red' ?>">

                            <i class="bi bi-image" style="font-size: 24px;"></i>                                <?= $comic['status'] === 'active' ? '激活' : '禁用' ?>

                        </div>                            </span>

                    <?php endif; ?>                        </p>

                                            <?php if (!empty($comic['images'])): ?>

                    <div class="comic-info">                            <p><strong>图片:</strong> <?= count($comic['images']) ?> 张</p>

                        <h4><?= htmlspecialchars($comic['title']) ?></h4>                        <?php endif; ?>

                        <p><strong>ID:</strong> <?= htmlspecialchars($comicId) ?></p>                    </div>

                        <p><strong>副标题:</strong> <?= htmlspecialchars($comic['subtitle']) ?></p>                    

                        <p><strong>状态:</strong>                     <div class="comic-actions">

                            <span style="color: <?= $comic['status'] === 'active' ? 'green' : 'red' ?>">                        <button class="btn btn-success" onclick="editComic('<?= htmlspecialchars($comicId) ?>')">

                                <?= $comic['status'] === 'active' ? '激活' : '禁用' ?>                            <i class="bi bi-pencil"></i> 编辑

                            </span>                        </button>

                        </p>                        <form method="post" style="display: inline;" 

                        <?php if (!empty($comic['images'])): ?>                              onsubmit="return confirm('确定要删除这个漫画吗？')">

                            <p><strong>图片:</strong> <?= count($comic['images']) ?> 张</p>                            <input type="hidden" name="action" value="delete">

                        <?php endif; ?>                            <input type="hidden" name="delete_id" value="<?= htmlspecialchars($comicId) ?>">

                        <?php if (!empty($comic['created_at'])): ?>                            <button type="submit" class="btn btn-danger">

                            <p><strong>创建:</strong> <?= htmlspecialchars($comic['created_at']) ?></p>                                <i class="bi bi-trash"></i> 删除

                        <?php endif; ?>                            </button>

                    </div>                        </form>

                                        </div>

                    <div class="comic-actions">                </div>

                        <button class="btn btn-success" onclick="editComic('<?= htmlspecialchars($comicId) ?>')">            <?php endforeach; ?>

                            <i class="bi bi-pencil"></i> 编辑        <?php endif; ?>

                        </button>    </div>

                        <form method="post" style="display: inline;" </div>

                              onsubmit="return confirm('确定要删除这个漫画吗？')">

                            <input type="hidden" name="action" value="delete"><script>

                            <input type="hidden" name="delete_id" value="<?= htmlspecialchars($comicId) ?>">// 存储漫画数据用于编辑

                            <button type="submit" class="btn btn-danger">const comicsData = <?= json_encode($allComics) ?>;

                                <i class="bi bi-trash"></i> 删除

                            </button>function resetForm() {

                        </form>    document.getElementById('comicForm').reset();

                    </div>    document.getElementById('formAction').value = 'add';

                </div>    document.getElementById('editId').value = '';

            <?php endforeach; ?>    document.querySelector('.comic-form h2').innerHTML = '<i class="bi bi-plus-circle"></i> 添加新漫画';

        <?php endif; ?>}

    </div>

</div>function editComic(comicId) {

    const comic = comicsData[comicId];

<script>    if (!comic) return;

// 存储漫画数据用于编辑    

const comicsData = <?= json_encode($allComics) ?>;    // 设置表单为编辑模式

    document.getElementById('formAction').value = 'edit';

function resetForm() {    document.getElementById('editId').value = comicId;

    document.getElementById('comicForm').reset();    document.querySelector('.comic-form h2').innerHTML = '<i class="bi bi-pencil"></i> 编辑漫画: ' + comic.title;

    document.getElementById('formAction').value = 'add';    

    document.getElementById('editId').value = '';    // 填充表单数据

    document.querySelector('.comic-form h2').innerHTML = '<i class="bi bi-plus-circle"></i> 添加新漫画';    document.getElementById('comic_id').value = comicId;

}    document.getElementById('title').value = comic.title || '';

    document.getElementById('subtitle').value = comic.subtitle || '';

function editComic(comicId) {    document.getElementById('lines').value = comic.lines || '';

    const comic = comicsData[comicId];    document.getElementById('alt').value = comic.alt || '';

    if (!comic) return;    document.getElementById('status').value = comic.status || 'active';

        

    // 设置表单为编辑模式    // 滚动到表单顶部

    document.getElementById('formAction').value = 'edit';    document.querySelector('.comic-form').scrollIntoView({ behavior: 'smooth' });

    document.getElementById('editId').value = comicId;}

    document.querySelector('.comic-form h2').innerHTML = '<i class="bi bi-pencil"></i> 编辑漫画: ' + comic.title;

    // 文件上传预览功能

    // 填充表单数据document.querySelectorAll('input[type="file"]').forEach(input => {

    document.getElementById('comic_id').value = comicId;    input.addEventListener('change', function(e) {

    document.getElementById('title').value = comic.title || '';        const files = e.target.files;

    document.getElementById('subtitle').value = comic.subtitle || '';        if (files.length > 0) {

    document.getElementById('lines').value = comic.lines || '';            console.log(`选择了 ${files.length} 个文件:`, Array.from(files).map(f => f.name));

    document.getElementById('alt').value = comic.alt || '';        }

    document.getElementById('status').value = comic.status || 'active';    });

    });

    // 滚动到表单顶部</script>

    document.querySelector('.comic-form').scrollIntoView({ behavior: 'smooth' });
}

// 文件上传预览功能
document.querySelectorAll('input[type="file"]').forEach(input => {
    input.addEventListener('change', function(e) {
        const files = e.target.files;
        if (files.length > 0) {
            console.log(`选择了 ${files.length} 个文件:`, Array.from(files).map(f => f.name));
            
            // 显示文件信息
            const parent = this.closest('.file-upload-area');
            let infoDiv = parent.querySelector('.file-info');
            if (!infoDiv) {
                infoDiv = document.createElement('div');
                infoDiv.className = 'file-info';
                infoDiv.style.cssText = 'margin-top: 10px; font-size: 12px; color: rgba(255,255,255,0.8);';
                parent.appendChild(infoDiv);
            }
            
            if (files.length === 1) {
                infoDiv.textContent = `已选择: ${files[0].name}`;
            } else {
                infoDiv.textContent = `已选择 ${files.length} 个文件`;
            }
        }
    });
});
</script>