#!/bin/bash

# Konfigurasi
DATE=$(date +"%Y-%m-%d_%H-%M")
BACKUP_DIR="/tmp/backup_$DATE"
WEB_DIR="/var/www/html"
DB_NAME="nama_database"
DB_USER="root"
DB_PASS="password"
REMOTE="gdrive:backup"
RETENTION_DAYS=7

# Telegram
BOT_TOKEN="ISI_BOT_TOKEN"
CHAT_ID="ISI_CHAT_ID"

# Nama file backup
BACKUP_FILE="backup_${DB_NAME}_${DATE}.zip"

# Fungsi kirim notif
function send_telegram {
    curl -s -X POST https://api.telegram.org/bot$BOT_TOKEN/sendMessage \
    -d chat_id=$CHAT_ID \
    -d text="$1"
}

# Buat folder sementara
mkdir -p $BACKUP_DIR

# Backup database
if ! mysqldump -u $DB_USER -p$DB_PASS $DB_NAME > $BACKUP_DIR/db.sql; then
    send_telegram "❌ Backup GAGAL: tidak bisa export database $DB_NAME"
    exit 1
fi

# Copy file website
if ! rsync -a $WEB_DIR $BACKUP_DIR/website; then
    send_telegram "❌ Backup GAGAL: tidak bisa copy file website"
    exit 1
fi

# Zip semua
cd /tmp
zip -r $BACKUP_FILE backup_$DATE >/dev/null

# Upload ke Google Drive
if rclone copy /tmp/$BACKUP_FILE $REMOTE --progress; then
    send_telegram "✅ Backup BERHASIL: $BACKUP_FILE sudah diupload ke Google Drive"
else
    send_telegram "❌ Backup GAGAL: upload ke Google Drive"
    exit 1
fi

# Hapus file sementara di lokal
rm -rf $BACKUP_DIR
rm /tmp/$BACKUP_FILE

# Hapus file lama di Google Drive (lebih dari 7 hari)
rclone delete --min-age ${RETENTION_DAYS}d $REMOTE
rclone rmdirs $REMOTE --leave-root
