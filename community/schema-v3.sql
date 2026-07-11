-- Community v3: Performance Indexes
-- Run once in phpMyAdmin (SQL tab, your database selected), AFTER schema-v2.sql.

CREATE INDEX idx_posts_channel_type_status ON posts (channel, type, status, approved_at, id);
