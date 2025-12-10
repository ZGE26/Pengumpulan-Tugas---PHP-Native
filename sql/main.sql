CREATE DATABASE IF NOT EXISTS academic_management;
USE academic_management;

CREATE TABLE role (
    role_id INT AUTO_INCREMENT PRIMARY KEY,
    role_name VARCHAR(50) NOT NULL
);
INSERT INTO role (role_id, role_name) VALUES 
(1, 'Dosen'),
(2, 'Mahasiswa');

CREATE TABLE user (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    role_id INT NOT NULL,
    user_name VARCHAR(100) NOT NULL,
    nama_lengkap VARCHAR(150) NOT NULL,
    nomor_induk VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    FOREIGN KEY (role_id) REFERENCES role(role_id) ON DELETE RESTRICT
);

CREATE TABLE mata_kuliah (
    id_matkul INT AUTO_INCREMENT PRIMARY KEY,
    id_dosen INT NOT NULL,
    nama_matkul VARCHAR(100) NOT NULL,
    kode_matkul VARCHAR(20) UNIQUE NOT NULL,
    FOREIGN KEY (id_dosen) REFERENCES user(user_id) ON DELETE CASCADE
);

CREATE TABLE enrollment (
    id_enrollment INT AUTO_INCREMENT PRIMARY KEY,
    id_matkul INT NOT NULL,
    id_mahasiswa INT NOT NULL,
    tanggal_ambil DATE NOT NULL,
    FOREIGN KEY (id_matkul) REFERENCES mata_kuliah(id_matkul) ON DELETE CASCADE,
    FOREIGN KEY (id_mahasiswa) REFERENCES user(user_id) ON DELETE CASCADE
);

CREATE TABLE tugas (
    id_tugas INT AUTO_INCREMENT PRIMARY KEY,
    id_matkul INT NOT NULL,
    judul_tugas VARCHAR(200) NOT NULL,
    deskripsi_tugas TEXT,
    bobot_nilai DECIMAL(5,2),
    deadline DATETIME NOT NULL,
    create_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    update_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_matkul) REFERENCES mata_kuliah(id_matkul) ON DELETE CASCADE
);

CREATE TABLE pengumpulan (
    id_pengumpulan INT AUTO_INCREMENT PRIMARY KEY,
    id_tugas INT NOT NULL,
    id_mahasiswa INT NOT NULL,
    nama_file VARCHAR(255) NOT NULL,
    tanggal_kumpul DATETIME DEFAULT CURRENT_TIMESTAMP,
    nilai DECIMAL(5,2),
    catatan_dosen TEXT,
    FOREIGN KEY (id_tugas) REFERENCES tugas(id_tugas) ON DELETE CASCADE,
    FOREIGN KEY (id_mahasiswa) REFERENCES user(user_id) ON DELETE CASCADE
);