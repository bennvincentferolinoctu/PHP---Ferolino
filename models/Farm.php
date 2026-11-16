<?php
class Farm {
    private Database $db;
    public function __construct(Database $db) { $this->db = $db; }
    public function all(array $filters = [], array $options = []): array { return $this->db->select('farm', '*', $filters, $options); }
    public function find(int $id): ?array { return $this->db->find('farm', $id, 'farm_id'); }
    public function create(array $data): array { return $this->db->insert('farm', $data); }
    public function update(int $id, array $data): array { return $this->db->update('farm', $data, ['farm_id=eq.' . $id]); }
    public function delete(int $id): array { return $this->db->delete('farm', ['farm_id=eq.' . $id]); }
    public function count(): int { return count($this->db->select('farm', 'farm_id')); }
}
?>