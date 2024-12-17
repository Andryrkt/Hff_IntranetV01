export class FileHandler {
  constructor(fileInput, fileList) {
    this.fileInput = fileInput;
    this.fileList = fileList;
    this.filesArray = [];
  }

  addFile(file) {
    if (
      !this.filesArray.some((f) => f.name === file.name && f.size === file.size)
    ) {
      this.filesArray.push(file);
      this.displayFile(file);
    }
  }

  displayFile(file) {
    const listItem = document.createElement('li');
    listItem.textContent = `${file.name} (${(file.size / 1024).toFixed(1)} Ko)`;
    this.fileList.appendChild(listItem);
  }

  removeFile(file) {
    this.filesArray = this.filesArray.filter((f) => f !== file);
  }
}
