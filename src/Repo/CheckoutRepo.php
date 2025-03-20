<?php

namespace MediaWiki\Extension\PageCheckout\Repo;

use MediaWiki\Extension\PageCheckout\Entity\CheckoutEntity;
use MediaWiki\Title\Title;
use MediaWiki\User\User;
use MWException;
use ObjectCacheFactory;
use Wikimedia\Rdbms\DBError;
use Wikimedia\Rdbms\IConnectionProvider;

class CheckoutRepo {

	/** @var IConnectionProvider */
	private $connectionProvider;

	/** @var ObjectCacheFactory */
	private $objectCacheFactory;

	/**
	 * @param IConnectionProvider $connectionProvider
	 * @param ObjectCacheFactory $objectCacheFactory
	 */
	public function __construct( IConnectionProvider $connectionProvider, ObjectCacheFactory $objectCacheFactory ) {
		$this->connectionProvider = $connectionProvider;
		$this->objectCacheFactory = $objectCacheFactory;
	}

	/**
	 * @param Title $title
	 * @return CheckoutEntity|null
	 */
	public function getForPage( Title $title ): ?CheckoutEntity {
		if ( !$title->exists() ) {
			return null;
		}
		$entities = $this->get( [
			'pcl_page_id' => $title->getArticleID()
		] );

		if ( !empty( $entities ) ) {
			return $entities[0];
		}

		return null;
	}

	/**
	 * @param User $user
	 * @return array
	 */
	public function getForUser( User $user ): array {
		if ( !$user->isRegistered() ) {
			return [];
		}
		return $this->get( [
			'pcl_user_id' => $user->getId()
		] );
	}

	/**
	 * @param CheckoutEntity $entity
	 * @return CheckoutEntity
	 */
	public function save( CheckoutEntity $entity ): CheckoutEntity {
		$dbw = $this->connectionProvider->getPrimaryDatabase();

		$data = [
			'pcl_page_id' => $entity->getTitle()->getArticleID(),
			'pcl_user_id' => $entity->getUser()->getId(),
			'pcl_payload' => json_encode( $entity->getPayload() ),
		];

		$res = $dbw->insert(
			'page_checkout_locks',
			$data,
			__METHOD__
		);
		if ( $res ) {
			$id = $dbw->insertId();
		}

		if ( !$res ) {
			throw new DBError( $dbw, 'pagecheckout-error-db-insert' );
		}

		$inserted = $this->get( [ 'pcl_id' => $id ] );
		if ( empty( $inserted ) ) {
			throw new DBError( $dbw, 'pagecheckout-error-db-retrieve-inserted' );
		}

		return array_shift( $inserted );
	}

	/**
	 * @param CheckoutEntity $entity
	 * @return bool
	 * @throws MWException
	 */
	public function delete( CheckoutEntity $entity ): bool {
		if ( !$entity->getId() ) {
			throw new MWException( 'pagecheckout-error-no-checkout-id' );
		}

		$dbw = $this->connectionProvider->getPrimaryDatabase();
		return $dbw->delete( 'page_checkout_locks', [ 'pcl_id' => $entity->getId() ], __METHOD__ );
	}

	/**
	 * @param array|null $conds
	 * @return array
	 */
	private function get( $conds = [] ) {
		$conds = array_merge( $conds, [
			'pcl_page_id = page_id',
			'pcl_user_id = user_id'
		] );

		$objectCache = $this->objectCacheFactory->getLocalServerInstance();
		$fname = __METHOD__;

		return $objectCache->getWithSetCallback(
			$objectCache->makeKey( 'pagecheckout-get', json_encode( $conds ) ),
			$objectCache::TTL_PROC_SHORT,
			function () use ( $conds, $fname ) {
				$dbr = $this->connectionProvider->getReplicaDatabase();

				$res = $dbr->newSelectQueryBuilder()
					->tables( [
						'page_checkout_locks',
						'page',
						'user'
					] )
					->fields( [
						'page_checkout_locks.pcl_id',
						'page_checkout_locks.pcl_payload',
						'page.page_namespace',
						'page.page_title',
						'user.user_id'
					] )
					->where( $conds )
					->caller( $fname )
					->fetchResultSet();

				$entities = [];
				foreach ( $res as $row ) {
					$title = Title::newFromRow( $row );
					$user = User::newFromRow( $row );
					$entities[] = new CheckoutEntity(
						$row->pcl_id,
						$title,
						$user,
						json_decode( $row->pcl_payload, 1 ) ?? []
					);
				}

				return $entities;
			}
		);
	}
}
