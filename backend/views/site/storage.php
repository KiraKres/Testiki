<?php
/** @var yii\web\View $this */
$this->title = 'Тестики - Облако';

// мой пиздатый стиль
$this->registerCssFile('@web/css/storage-style.css?v=' . time());

// внешние библиотеки
$this->registerJsFile('https://cdn.jsdelivr.net/npm/vue@2.6.14/dist/vue.js', ['position' => \yii\web\View::POS_HEAD]);
$this->registerJsFile('https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js', ['position' => \yii\web\View::POS_HEAD]);
$this->registerJsFile('https://cdn.jsdelivr.net/npm/sweetalert2@11', ['position' => \yii\web\View::POS_HEAD]);

// вие2 - логика
$this->registerJsFile('@web/js/storage-app.js', ['depends' => [\yii\web\JqueryAsset::class]]);
?>

<div id="storage-app" v-cloak>
    <header class="main-header">
        <div class="logo">
            <img src="/img/dino_storage.svg" alt="Logo" width="30">
            <span>Testiki</span>
        </div>
        <div class="user-profile">
            <div class="avatar">K</div>
            <span class="username">Kira</span>
            <a href="<?= \yii\helpers\Url::to(['site/logout']) ?>" class="logout-link">Logout</a>
        </div>
    </header>

    <div class="content-card">
        <h2>Upload File</h2>
        
        <div class="upload-section">
            <label class="custom-file-upload">
                <input type="file" @change="handleFileUpload" style="display:none;">
                Выбор файла
            </label>
            <span class="file-status">
                {{ selectedFile ? selectedFile.name : 'Не выбран ни один файл' }}
            </span>
            
            <div class="upload-right-group">
                <div class="input-with-label">
                    <label>Enter file name</label>
                    <input type="text" v-model="fileName" class="custom-input" placeholder="Custom name">
                </div>
                
                <button @click="uploadFile" class="btn-upload-submit">
                    <img src="/img/Upload.svg" alt="" class="upload-icon">
                    <span>Upload</span>
                </button>
            </div>
        </div>

        <div class="table-container">
            <h3>FILES</h3>
            <table class="files-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>FILE NAME</th>
                        <th>AUTHOR</th>
                        <th>LAST MODIFIED</th>
                        <th class="actions-header-td">
                            <div class="actions-header-wrapper">ACTIONS</div>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="file in files" :key="file.id">
                        <td>{{ file.id }}</td>
                        
                        <td class="file-name-td">
                            <div class="file-name-wrapper">
                                <img src="/img/File.svg" alt="" class="table-file-icon">
                                <span>{{ file.file_name }}</span>
                            </div>
                        </td>
                        
                        <td>{{ file.user_name }}</td>
                        <td>{{ file.time_modify }}</td>
                        
                        <td class="actions-td">
                            <div class="actions-wrapper">
                                <button @click="downloadFile(file.id)" class="btn-action btn-download" title="Скачать">
                                    <img src="/img/Download.svg" alt="Download">
                                </button>
                                <button @click="editFile(file.id)" class="btn-action btn-edit" title="Редактировать">
                                    <img src="/img/Edit 2.svg" alt="Edit">
                                </button>
                                <button @click="deleteFile(file.id)" class="btn-action btn-delete" title="Удалить">
                                    <img src="/img/Trash.svg" alt="Delete">
                                </button>
                            </div> 
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <img src="/img/leaf.svg" class="leaf-decor" alt="">
</div>

<!-- декорчик -->

<div class="circle1"></div>
<div class="circle2"></div>
<div class="circle3"></div>
<div class="rect"></div>