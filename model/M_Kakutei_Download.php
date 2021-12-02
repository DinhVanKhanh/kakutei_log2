<?php
require_once __DIR__ . '/../config/database.php';

class M_Kakutei_Download extends Database
{
	protected $table = 'Kakutei_Download';

	public function add(array $data)
	{
		extract($data);
		$rs = [];

		$this->conn->beginTransaction();

		try {
			$query = "INSERT INTO $this->table (DownloadDate, DownloadProgramName, DownloadProgramYear, DownloadProgramVersion, UserClassification, UserSerialNumber, UserIDPartner, AdminComment, AdminCommentDate, DeleteFlag, DeleteFlagDate) VALUES (:DownloadDate, :DownloadProgramName, :DownloadProgramYear, :DownloadProgramVersion, :UserClassification, :UserSerialNumber, :UserIDPartner, :AdminComment, :AdminCommentDate, '', '')";
			$stmt = $this->conn->prepare($query);
			$stmt->bindParam(":DownloadDate", $DownloadDate);
			$stmt->bindParam(":DownloadProgramName", $DownloadProgramName);
			$stmt->bindParam(":DownloadProgramYear", $DownloadProgramYear);
			$stmt->bindParam(":DownloadProgramVersion", $DownloadProgramVersion);
			$stmt->bindParam(":UserClassification", $UserClassification);
			$stmt->bindParam(":UserSerialNumber", $UserSerialNumber);
			$stmt->bindParam(":UserIDPartner", $UserIDPartner);
			$stmt->bindParam(":AdminComment", $AdminComment);
			$stmt->bindParam(":AdminCommentDate", $AdminCommentDate);
			$stmt->execute();

			$this->conn->commit();
		} catch (PDOException $e) {
			$this->conn->rollback();
			$rs['query'] = "INSERT INTO {$this->table} VALUES ($DownloadDate, $DownloadProgramName, $DownloadProgramYear, $DownloadProgramVersion, $UserClassification, $UserSerialNumber, $UserIDPartner, $AdminComment, $AdminCommentDate, '', '')";
			$rs['error'] = $e->getMessage();
		}

		return $rs;
	}
	public function addOptimize($data)
	{
		$rs = [];

		$this->conn->beginTransaction();

		try {

			$query = "INSERT INTO $this->table (DownloadDate, DownloadProgramName, DownloadProgramYear, DownloadProgramVersion, UserClassification, UserSerialNumber, UserIDPartner, AdminComment, AdminCommentDate, DeleteFlag, DeleteFlagDate,UserIPAddress) ";
			$query .= "VALUES ";
			$query .= $data;
			$stmt = $this->conn->prepare($query);
			$stmt->execute();

			$this->conn->commit();

			$rs['query'] = $query;
		} catch (Exception $e) {
			$this->conn->rollback();
			$rs['query'] = $query;
			$rs['error'] = $e->getMessage();
		}

		return $rs;
	}
	public function getById(int $id)
	{
		$stmt = $this->conn->prepare("SELECT FROM $this->table WHERE id = :id");
		$stmt->bindParam(":id", $id, PDO::PARAM_INT, 11);
		$stmt->execute();
		return $stmt->fetch(PDO::FETCH_ASSOC);
	}

	public function update(array $data)
	{
	}

	public function delete(int $id)
	{
	}
}
