new Vue({
    el: '#storage-app',
    data: {
        files: [],
        selectedFile: null,
        fileName: ''
    },
    mounted() {
        this.loadFiles();
    },
    methods: {
        loadFiles() {
            axios.get('/file/index')
                .then(res => {
                    //праблем с притти юрл, фикс - просмотр ошибок
                    // если пришел HTML (начинается с <!DOCTYPE или <html), значит сервер отдал страницу вместо данных
                    if (typeof res.data === 'string' && res.data.trim().startsWith('<')) {
                        console.error("Ошибка: сервер вернул HTML вместо JSON.");
                        this.files = []; 
                        return;
                    }
                    this.files = res.data;
                })
                .catch(err => console.log("Ошибка загрузки:", err));
        },
        handleFileUpload(event) {
            this.selectedFile = event.target.files[0];
            if (!this.fileName) this.fileName = this.selectedFile.name;
        },
        uploadFile() {
            if (!this.selectedFile) return alert('Выберите файл!');
            
            let formData = new FormData();
            formData.append('file', this.selectedFile);
            formData.append('file_name', this.fileName);

            axios.post('/file/upload', formData).then(() => {
                this.loadFiles();
                this.selectedFile = null;
                this.fileName = '';
            });
        },
        deleteFile(id) {
            if(confirm('Точно удалить?')) {
                axios.post('/file/delete?id=' + id).then(() => {
                    this.loadFiles();
                });
            }
        },

        editFile(id) {
        // ИСПРАВИТЬ НЕ ЗАБЫТЬ!!! СМЕНИТЬ АЙДИ НА ПРОСТО НУМЕРАЦИЮ
        const file = this.files.find(f => f.id === id);
        
        Swal.fire({
            title: 'Rename File',
            input: 'text',
            inputValue: file.file_name,
            showCancelButton: true,
            confirmButtonText: 'Save',
            confirmButtonColor: '#354F52',
            inputValidator: (value) => {
                if (!value) {
                    return 'You need to write something!'
                }
            }
        }).then((result) => {
            if (result.isConfirmed) {
                this.updateFileName(id, result.value);
            }
        });
        },

        updateFileName(id, newName) {
        axios.post('/site/rename-file', {
            id: id,
            newName: newName
        })
        .then(response => {
            if (response.data.success) {
                // обновляем имя в массиве Vue чтобы страница не перезагружалась
                const file = this.files.find(f => f.id === id);
                file.file_name = response.data.finalName;
                
                Swal.fire('Saved!', `New name: ${response.data.finalName}`, 'success');
            }else {
            Swal.fire('Error', 'File not found or access denied', 'error');
        }
        })
        .catch(error => {
            Swal.fire('Error', 'Could not rename file', 'error');
        });
        },

        downloadFile(id) {
            // Формируем URL для скачивания
            const url = '/file/download/' + id;
            
            // Создаем невидимую ссылку, чтобы браузер начал загрузку
            const link = document.createElement('a');
            link.href = url;
            link.setAttribute('download', ''); // Указываем, что это скачивание
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        },
    }
});