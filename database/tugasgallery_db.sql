-- ============================================
-- Gallery Photo Management System Database
-- ============================================
-- Description: Database schema for a photo gallery web application
-- with user authentication, albums, photos, comments, and likes
-- Created: 2025-12-09
-- ============================================

-- Drop database if exists (for clean reinstallation)
DROP DATABASE IF EXISTS tugasgallery_db;

-- Create database with UTF8MB4 support for international characters and emojis
CREATE DATABASE tugasgallery_db 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

-- Use the database
USE tugasgallery_db;

-- ============================================
-- Table: gallery_user
-- Purpose: User authentication and profile management
-- ============================================
CREATE TABLE gallery_user (
    UserID INT(11) NOT NULL AUTO_INCREMENT,
    Username VARCHAR(255) NOT NULL,
    Password VARCHAR(255) NOT NULL COMMENT 'Stores hashed passwords (bcrypt recommended)',
    Email VARCHAR(255) NOT NULL,
    NamaLengkap VARCHAR(255) NOT NULL COMMENT 'Full name',
    Alamat TEXT DEFAULT NULL COMMENT 'Address (optional)',
    Level ENUM('Admin', 'User') NOT NULL DEFAULT 'User' COMMENT 'User role for access control',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Registration timestamp',
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Last profile update',
    
    PRIMARY KEY (UserID),
    UNIQUE KEY unique_username (Username),
    UNIQUE KEY unique_email (Email),
    KEY idx_email (Email)
) ENGINE=InnoDB 
  DEFAULT CHARSET=utf8mb4 
  COLLATE=utf8mb4_unicode_ci
  COMMENT='User authentication and profile information';

-- ============================================
-- Table: gallery_album
-- Purpose: Photo album organization
-- ============================================
CREATE TABLE gallery_album (
    AlbumID INT(11) NOT NULL AUTO_INCREMENT,
    NamaAlbum VARCHAR(255) NOT NULL COMMENT 'Album name',
    Deskripsi TEXT DEFAULT NULL COMMENT 'Album description (optional)',
    TanggalDibuat DATE NOT NULL COMMENT 'Creation date',
    UserID INT(11) NOT NULL COMMENT 'Album owner',
    is_public BOOLEAN NOT NULL DEFAULT TRUE COMMENT 'Album visibility control',
    cover_photo_id INT(11) DEFAULT NULL COMMENT 'Featured cover photo reference',
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    PRIMARY KEY (AlbumID),
    KEY idx_user (UserID),
    KEY idx_cover_photo (cover_photo_id)
) ENGINE=InnoDB 
  DEFAULT CHARSET=utf8mb4 
  COLLATE=utf8mb4_unicode_ci
  COMMENT='Photo album collections';

-- ============================================
-- Table: gallery_foto
-- Purpose: Photo metadata and file information
-- ============================================
CREATE TABLE gallery_foto (
    FotoID INT(11) NOT NULL AUTO_INCREMENT,
    JudulFoto VARCHAR(255) NOT NULL COMMENT 'Photo title',
    DeskripsiFoto TEXT DEFAULT NULL COMMENT 'Photo description (optional)',
    TanggalUnggah DATE NOT NULL COMMENT 'Upload date',
    LokasiFile VARCHAR(255) NOT NULL COMMENT 'File path or URL',
    AlbumID INT(11) DEFAULT NULL COMMENT 'Optional album association',
    UserID INT(11) NOT NULL COMMENT 'Photo owner',
    is_public BOOLEAN NOT NULL DEFAULT TRUE COMMENT 'Photo visibility control',
    view_count INT(11) NOT NULL DEFAULT 0 COMMENT 'Track photo views',
    file_size INT(11) DEFAULT NULL COMMENT 'File size in bytes',
    file_type VARCHAR(50) DEFAULT NULL COMMENT 'Image format (jpg, png, gif, etc.)',
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    PRIMARY KEY (FotoID),
    KEY idx_user (UserID),
    KEY idx_album (AlbumID),
    KEY idx_public (is_public)
) ENGINE=InnoDB 
  DEFAULT CHARSET=utf8mb4 
  COLLATE=utf8mb4_unicode_ci
  COMMENT='Photo metadata and file storage information';

-- ============================================
-- Table: gallery_komentarfoto
-- Purpose: Photo comments system
-- ============================================
CREATE TABLE gallery_komentarfoto (
    KomentarID INT(11) NOT NULL AUTO_INCREMENT,
    FotoID INT(11) NOT NULL COMMENT 'Reference to photo',
    UserID INT(11) NOT NULL COMMENT 'Comment author',
    IsiKomentar TEXT NOT NULL COMMENT 'Comment content',
    TanggalKomentar DATE NOT NULL COMMENT 'Comment date',
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Edit tracking',
    
    PRIMARY KEY (KomentarID),
    KEY idx_foto (FotoID),
    KEY idx_user (UserID)
) ENGINE=InnoDB 
  DEFAULT CHARSET=utf8mb4 
  COLLATE=utf8mb4_unicode_ci
  COMMENT='User comments on photos';

-- ============================================
-- Table: gallery_likefoto
-- Purpose: Photo like/favorite system
-- ============================================
CREATE TABLE gallery_likefoto (
    LikeID INT(11) NOT NULL AUTO_INCREMENT,
    FotoID INT(11) NOT NULL COMMENT 'Reference to photo',
    UserID INT(11) NOT NULL COMMENT 'User who liked',
    TanggalLike DATE NOT NULL COMMENT 'When like was given',
    
    PRIMARY KEY (LikeID),
    UNIQUE KEY unique_user_like (FotoID, UserID) COMMENT 'Prevent duplicate likes',
    KEY idx_foto (FotoID),
    KEY idx_user (UserID)
) ENGINE=InnoDB 
  DEFAULT CHARSET=utf8mb4 
  COLLATE=utf8mb4_unicode_ci
  COMMENT='Photo likes tracking (one per user per photo)';

-- ============================================
-- Foreign Key Constraints
-- ============================================

-- Album constraints
ALTER TABLE gallery_album
    ADD CONSTRAINT fk_album_user 
        FOREIGN KEY (UserID) 
        REFERENCES gallery_user(UserID) 
        ON DELETE CASCADE 
        ON UPDATE CASCADE;

-- Note: cover_photo_id FK added after gallery_foto table exists

-- Photo constraints
ALTER TABLE gallery_foto
    ADD CONSTRAINT fk_foto_user 
        FOREIGN KEY (UserID) 
        REFERENCES gallery_user(UserID) 
        ON DELETE CASCADE 
        ON UPDATE CASCADE,
    ADD CONSTRAINT fk_foto_album 
        FOREIGN KEY (AlbumID) 
        REFERENCES gallery_album(AlbumID) 
        ON DELETE SET NULL 
        ON UPDATE CASCADE;

-- Album cover photo constraint (added after gallery_foto exists)
ALTER TABLE gallery_album
    ADD CONSTRAINT fk_album_cover_photo 
        FOREIGN KEY (cover_photo_id) 
        REFERENCES gallery_foto(FotoID) 
        ON DELETE SET NULL 
        ON UPDATE CASCADE;

-- Comment constraints
ALTER TABLE gallery_komentarfoto
    ADD CONSTRAINT fk_komentar_foto 
        FOREIGN KEY (FotoID) 
        REFERENCES gallery_foto(FotoID) 
        ON DELETE CASCADE 
        ON UPDATE CASCADE,
    ADD CONSTRAINT fk_komentar_user 
        FOREIGN KEY (UserID) 
        REFERENCES gallery_user(UserID) 
        ON DELETE CASCADE 
        ON UPDATE CASCADE;

-- Like constraints
ALTER TABLE gallery_likefoto
    ADD CONSTRAINT fk_like_foto 
        FOREIGN KEY (FotoID) 
        REFERENCES gallery_foto(FotoID) 
        ON DELETE CASCADE 
        ON UPDATE CASCADE,
    ADD CONSTRAINT fk_like_user 
        FOREIGN KEY (UserID) 
        REFERENCES gallery_user(UserID) 
        ON DELETE CASCADE 
        ON UPDATE CASCADE;

-- ============================================
-- Sample Data (Optional - for testing)
-- ============================================

-- Insert test admin user (password: 'admin123' - should be hashed in production)
INSERT INTO gallery_user (Username, Password, Email, NamaLengkap, Alamat, Level) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@tugasgallery.com', 'Administrator', 'Jl. Admin No. 1', 'Admin');

-- Insert test regular user (password: 'user123' - should be hashed in production)
INSERT INTO gallery_user (Username, Password, Email, NamaLengkap, Alamat, Level) VALUES
('johndoe', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'john@example.com', 'John Doe', 'Jl. User No. 2', 'User');

-- Insert test album
INSERT INTO gallery_album (NamaAlbum, Deskripsi, TanggalDibuat, UserID, is_public) VALUES
('My First Album', 'Collection of my favorite photos', CURDATE(), 2, TRUE);

-- Insert test photo (with album)
INSERT INTO gallery_foto (JudulFoto, DeskripsiFoto, TanggalUnggah, LokasiFile, AlbumID, UserID, file_type) VALUES
('Sunset Beach', 'Beautiful sunset at the beach', CURDATE(), '/uploads/sunset_beach.jpg', 1, 2, 'jpg');

-- Insert test photo (without album - orphaned)
INSERT INTO gallery_foto (JudulFoto, DeskripsiFoto, TanggalUnggah, LokasiFile, AlbumID, UserID, file_type) VALUES
('Mountain View', 'Scenic mountain landscape', CURDATE(), '/uploads/mountain.jpg', NULL, 2, 'jpg');

-- Insert test comment
INSERT INTO gallery_komentarfoto (FotoID, UserID, IsiKomentar, TanggalKomentar) VALUES
(1, 1, 'Amazing photo! Love the colors.', CURDATE());

-- Insert test like
INSERT INTO gallery_likefoto (FotoID, UserID, TanggalLike) VALUES
(1, 1, CURDATE());

-- ============================================
-- Database Setup Complete
-- ============================================
-- To verify: SELECT * FROM information_schema.tables WHERE table_schema = 'tugasgallery_db';
-- To check constraints: SELECT * FROM information_schema.table_constraints WHERE table_schema = 'tugasgallery_db';
