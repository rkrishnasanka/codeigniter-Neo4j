<?php
namespace Everyman\Neo4j;

/**
 * Represents a single node in the database
 */
class Node extends PropertyContainer
{
	/**
	 * Delete this node
	 *
	 * @return PropertyContainer
	 * @throws Exception on failure
	 */
	public function delete()
	{
		$this->client->deleteNode($this);
		return $this;
	}

	/**
	 * Find paths from this node to the given node
	 *
	 * @param Node $to
	 * @param string $type
	 * @param string $dir
	 * @return PathFinder
	 */
	public function findPathsTo(Node $to, $type=null, $dir=null)
	{
		$finder = new PathFinder($this->client);
		$finder->setStartNode($this);
		$finder->setEndNode($to);
		if ($dir) {
			$finder->setDirection($dir);
		}

		if ($type) {
			$finder->setType($type);
		}

		return $finder;
	}

	/**
	 * Get the first relationship of this node that matches the given criteria
	 *
	 * @param mixed  $types string or array of strings
	 * @param string $dir
	 * @return Relationship
	 */
	public function getFirstRelationship($types=array(), $dir=null)
	{
		$rels = $this->client->getNodeRelationships($this, $types, $dir);
		if (count($rels) < 1) {
			return null;
		}
		return $rels[0];
	}

	/**
	 * Get relationships of this node
	 *
	 * @param mixed  $types string or array of strings
	 * @param string $dir
	 * @return array of Relationship
	 */
	public function getRelationships($types=array(), $dir=null)
	{
		return $this->client->getNodeRelationships($this, $types, $dir);
	}

	/**
	 * Load this node
	 *
	 * @return PropertyContainer
	 * @throws Exception on failure
	 */
	public function load()
	{
		$this->client->loadNode($this);
		return $this;
	}

	/**
	 * Make a new relationship
	 *
	 * @param Node $to
	 * @param string $type
	 * @return Relationship
	 */
	public function relateTo(Node $to, $type)
	{
		$rel = $this->client->makeRelationship();
		$rel->setStartNode($this);
		$rel->setEndNode($to);
		$rel->setType($type);

		return $rel;
	}

	/**
	 * Save this node
	 *
	 * @return PropertyContainer
	 * @throws Exception on failure
	 */
	public function save()
	{
		$this->client->saveNode($this);
		$this->useLazyLoad(false);
		return $this;
	}
}
