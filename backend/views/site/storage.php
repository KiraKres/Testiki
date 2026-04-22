<?php
$this->title = 'Тестики';
$this->registerCssFile('@web/css/cloud-style.css?v=' . time()); //подключение к стилям
?>

<div id="app" class="container">
    <div class="decor-container">
        <img src="/img/leaf.svg" class="decor-leaf" alt="">
        <div class="decor-circle-1"></div>
        <div class="decor-circle-2"></div>
        <div class="decor-rect"></div>
    </div>

    <header class="main-header">
        </header>

    <div class="content-wrapper">
        <h1>{{ message }}</h1>
        </div>
    </div>
    
    <header class="main-header">
        <div class="logo">
            <img src="/img/logo.svg" alt="Testiki">
            <span>Testiki</span>
        </div>
        <div class="user-profile">
            <div class="avatar">K</div>
            <span class="username">Kira</span>
            <a href="/index.php?r=site/logout" class="logout-link">Logout</a>
        </div>
    </header>
    <h1>{{ message }}</h1>
    
    <div class="upload-section">
        <input type="file" @change="handleFileUpload">
        <input type="text" v-model="fileName" placeholder="Название файла">
        <button @click="uploadFile" :disabled="!selectedFile">Загрузить</button>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Название</th>
                <th>Дата</th>
                <th>Действия</th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="file in files" :key="file.id">
                <td>{{ file.id }}</td>
                <td>{{ file.file_name }}</td>
                <td>{{ file.time_modify }}</td>
                <td>
                    <button @click="deleteFile(file.id)">Удалить</button>
                </td>
            </tr>
        </tbody>
        
    </table>
</div>

<script src="https://cdn.jsdelivr.net/npm/vue@2.6.14/dist/vue.js"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

<script>
new Vue({
    el: '#app',
    data: {
        message: 'Upload file',
        files: [],
        selectedFile: null,
        fileName: ''
    },
    mounted() {
        this.loadFiles(); // загружаем список при старте
    },
    methods: {
        loadFiles() {
            axios.get('/index.php?r=file/index').then(res => {
                this.files = res.data;
            });
        },
        handleFileUpload(event) {
            this.selectedFile = event.target.files[0];
            if (!this.fileName) this.fileName = this.selectedFile.name;
        },
        uploadFile() {
            let formData = new FormData();
            formData.append('file', this.selectedFile);
            formData.append('file_name', this.fileName);

            axios.post('/index.php?r=file/upload', formData).then(() => {
                this.loadFiles(); // обновляем таблицу
                this.selectedFile = null;
                this.fileName = '';
            });
        },
        deleteFile(id) {
            if(confirm('Точно удалить?')) {
                axios.post('/index.php?r=file/delete&id=' + id).then(() => {
                    this.loadFiles();
                });
            }
        }
    }
});
</script>