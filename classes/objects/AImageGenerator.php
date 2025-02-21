<?php

namespace objects;

//class AImageGenerator {
//    private int $id = 0;
//
//    private int $userId = 0;
//
//    private string $prompt;
//
//    private string $imageIdentifier;
//
//    private DateTime $created_at;
//    private ?DateTime $updated_at;
//
//    private int $objId = 0;
//
//    /**
//     * @throws Exception
//     */
//    public function __construct(?int $id = null) {
//        global $DIC;
//
//        $this->created_at = new DateTime();
//        $this->updated_at = new DateTime();
//        $this->userId = $DIC->user()->getId();
//
//        if($id !== null && $id > 0) {
//            $this->id = $id;
//            $this->loadFromDB();
//        }
//    }
//
//    /**
//     * @throws \Exception
//     */
//    public function loadFromDB(): void
//    {
//
//        $database = new AImageGeneratorDatabase();
//        $result = $database->select("aimage_generator", ["id" => $this->getId()]);
//
//        $this->setPrompt();
//        $this->setImageIdentifier();
//
//        if (isset($result[0])) {
//            $this->setObjId((int)$result[0]["obj_id"]);
//            $this->setUserId((int) $result[0]["user_id"]);
//            $this->setPrompt((string) $result[0]["prompt"]);
//            $this->setImageIdentifier((string) $result[0]["image_identifier"]);
//            $this->setCreatedAt(new DateTime($result[0]["created_at"]));
//            $this->setUpdatedAt(new DateTime($result[0]["updated_at"]));
//        }
//    }
//
//    /**
//     * @throws Exception
//     */
//    public function save(): AImageGenerator
//    {
//        $database = new AImageGeneratorDatabase();
//
//        $data = [
//            "prompt" => $this->getPrompt(),
//            "image_identifier" => $this->getImageIdentifier(),
//            "user_id" => $this->getUserId(),
//            "obj_id" => $this->getObjId(),
//            "created_at" => $this->getCreatedAt()->format("Y-m-d H:i:s"),
//            "updated_at" => $this->getUpdatedAt()->format("Y-m-d H:i:s")
//        ];
//
//        if ($this->getId() > 0) {
//            $database->update("aimage_generator", $data, ["id" => $this->getId()]);
//        } else {
//            $id = $database->nextId("aimage_generator");
//
//            $this->setId($id);
//
//            $data["id"] = $id;
//            $database->insert("aimage_generator", $data);
//        }
//        return $this;
//    }
//
//    /**
//     * @throws Exception
//     */
//    public function remove(): void
//    {
//        $database = new AImageGeneratorDatabase();
//        $database->delete("aimage_generator", ["id" => $this->getId()]);
//    }
//
//    /**
//     * @throws Exception
//     */
//    public function delete(): void
//    {
//        $database = new AImageGeneratorDatabase();
//
//        $database->delete("aimage_generator", ["id" => $this->getId()]);
//
//    }
//
//    public function getId(): int
//    {
//        return $this->id;
//    }
//
//    public function setId(int $id): void
//    {
//        $this->id = $id;
//    }
//
//    public function getPrompt(): string
//    {
//        return $this->prompt;
//    }
//
//    public function setPrompt(?string $prompt = null): void
//    {
//        $this->prompt = $prompt ?? "";
//    }
//
//    public function getImageIdentifier(): string
//    {
//        return $this->imageIdentifier;
//    }
//
//    public function setImageIdentifier(?string $imageIdentifier = null): void
//    {
//        $this->imageIdentifier = $imageIdentifier ?? "";
//    }
//
//    public function getCreatedAt(): \DateTime
//    {
//        return $this->created_at;
//    }
//
//    public function setCreatedAt(\DateTime $created_at): void
//    {
//        $this->created_at = $created_at;
//    }
//
//    public function getUpdatedAt(): ?\DateTime
//    {
//        return $this->updated_at;
//    }
//
//    public function setUpdatedAt(?\DateTime $updated_at): void
//    {
//        $this->updated_at = $updated_at;
//    }
//
//    public function getObjId(): int
//    {
//        return $this->objId;
//    }
//
//    public function setObjId(int $objId): void
//    {
//        $this->objId = $objId;
//    }
//
//    public function getUserId(): int
//    {
//        return $this->userId;
//    }
//
//    public function setUserId(int $userId): void
//    {
//        $this->userId = $userId;
//    }
//
//
//
//
//
//}