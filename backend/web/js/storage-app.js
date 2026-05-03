new Vue({
    el: '#storage-app',
    data: {
        files: [],
        selectedFile: null,
        fileName: '',
        currentUsername: 'Guest'
    },
    mounted() {
        //ник пользователя
        const nameFromData = this.$el.getAttribute('data-username');
        if (nameFromData) {
            this.currentUsername = nameFromData;
        }
        
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
            if (this.selectedFile && !this.fileName) {
                this.fileName = this.selectedFile.name;
            }
            //чтобы можно было один и тот же файл два раза подряд разгузить - проблема с инпутом
            this.$refs.fileInput.value = '';
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
        // чтобы юзер не делал битые файлы
        const file = this.files.find(f => f.id === id);
        const fileName = file.file_name;
        const lastDotIndex = fileName.lastIndexOf('.');
        const nameWithoutExt = lastDotIndex !== -1 
            ? fileName.substring(0, lastDotIndex) 
            : fileName;
        
        Swal.fire({
            title: 'Rename File',
            input: 'text',
            inputValue: nameWithoutExt,
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
            axios.post('/site/rename-file', { id: id, newName: newName })
            .then(response => {
                if (response.data.success) {
                    const file = this.files.find(f => f.id === id);
                    if (file) {
                        file.file_name = response.data.finalName;
                        file.time_modify = response.data.newTime; // ОБНОВЛЯЕМ ДАТУ ТУТ
                        Swal.fire('Saved!', `New name: ${response.data.finalName}`, 'success');
                    }
                } else {
                    Swal.fire('Error', response.data.error || 'Access denied', 'error');
                }
            })
            .catch(error => {
                console.error(error);
                Swal.fire('Error', 'Could not rename file', 'error');
            });
        },

        downloadFile(id) {
            // скачать
            const url = '/file/download/' + id;
            
            const link = document.createElement('a');
            link.href = url;
            link.setAttribute('download', '');
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        },

        showFullName(name) {
            Swal.fire({
                title: 'Полное имя файла',
                text: name,
                icon: 'info',
                confirmButtonText: 'Окей',
                confirmButtonColor: '#436063' 
            });
        },
    }
});