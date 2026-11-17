<?php
class User {
    private Database $db;
    public function __construct(Database $db) { $this->db = $db; }
    public function all(array $filters = [], array $options = []): array { return $this->db->select('app_user', '*', $filters, $options); }
    public function find(int $id): ?array { return $this->db->find('app_user', $id, 'user_id'); }
    public function create(array $data): array { return $this->db->insert('app_user', $data); }
    public function update(int $id, array $data): array { return $this->db->update('app_user', $data, ['user_id=eq.' . $id]); }
    public function delete(int $id): array { return $this->db->delete('app_user', ['user_id=eq.' . $id]); }
    public function count(): int { return count($this->db->select('app_user', 'user_id')); }
}
?>