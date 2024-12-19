<?php

namespace App\Service\fichier;

class JsonFileService
{
    private $filePath;
    private $data = [];

    /**
     * Constructeur, prend en paramètre le chemin vers le fichier JSON.
     */
    public function __construct($filePath)
    {
        $this->filePath = $filePath;
        $this->load();
    }

    /**
     * Charge le contenu du fichier JSON dans la propriété $data.
     */
    private function load()
    {
        if (!file_exists($this->filePath)) {
            // Si le fichier n'existe pas, on initialise $data à un tableau vide
            $this->data = [];
            return;
        }

        $jsonContent = file_get_contents($this->filePath);
        $decoded = json_decode($jsonContent, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException("Erreur de parsing du JSON : " . json_last_error_msg());
        }

        $this->data = is_array($decoded) ? $decoded : [];
    }

    /**
     * Sauvegarde le contenu de $this->data dans le fichier JSON.
     */
    public function save()
    {
        $jsonContent = json_encode($this->data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        if ($jsonContent === false) {
            throw new \RuntimeException("Impossible d'encoder les données en JSON.");
        }

        file_put_contents($this->filePath, $jsonContent);
    }

    /**
     * Récupère l'ensemble des données chargées.
     */
    public function getAllData()
    {
        return $this->data;
    }

    /**
     * Récupère les éléments d'une section (clé) donnée.
     * Retourne null si la clé n'existe pas.
     */
    public function getSection($key)
    {
        return $this->data[$key] ?? null;
    }

    /**
     * Ajoute ou met à jour une section complète.
     * $value doit être un tableau, par exemple ["AGR", "ATC", "AUS"].
     */
    public function setSection($key, array $value)
    {
        $this->data[$key] = $value;
    }

    /**
     * Ajoute un élément à une section si celle-ci existe et que l'élément n'est pas déjà présent.
     */
    public function addElementToSection($section, $element)
    {
        if (!isset($this->data[$section])) {
            $this->data[$section] = [];
        }

        if (!in_array($element, $this->data[$section])) {
            $this->data[$section][] = $element;
        }
    }

    /**
     * Supprime un élément d'une section donnée.
     */
    public function removeElementFromSection($section, $element)
    {
        if (isset($this->data[$section])) {
            $this->data[$section] = array_filter($this->data[$section], function ($e) use ($element) {
                return $e !== $element;
            });
        }
    }

}