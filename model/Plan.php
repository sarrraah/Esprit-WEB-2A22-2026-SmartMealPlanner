<?php

require_once __DIR__ . '/PlanDbStore.php';

class Plan
{
    public function __construct(
        public int    $id,
        public string $nom,
        public int    $duree,
        public string $dateDebut,
        public string $dateFin,
        public string $objectif,
        public string $description,
        public int    $userId
    ) {}

    public static function fromRow(array $r): self
    {
        return new self(
            (int)    ($r['id_plan']     ?? 0),
            (string) ($r['nom']         ?? ''),
            (int)    ($r['duree']       ?? 7),
            (string) ($r['date_debut']  ?? ''),
            (string) ($r['date_fin']    ?? ''),
            (string) ($r['objectif']    ?? ''),
            (string) ($r['description'] ?? ''),
            (int)    ($r['user_id']     ?? 1),
        );
    }

    public function progressPercent(): int
    {
        if ($this->duree <= 0 || !$this->dateDebut) return 0;
        $elapsed = max(0, (int) floor((time() - strtotime($this->dateDebut)) / 86400));
        return min(100, (int) round(($elapsed / $this->duree) * 100));
    }

    public function daysElapsed(): int
    {
        if (!$this->dateDebut) return 0;
        return min($this->duree, max(0, (int) floor((time() - strtotime($this->dateDebut)) / 86400)));
    }

    public static function first(): ?self  { return PlanDbStore::first(); }

    public static function find(int $id): ?self
    {
        foreach (PlanDbStore::all() as $p) {
            if ($p->id === $id) return $p;
        }
        return null;
    }
}
