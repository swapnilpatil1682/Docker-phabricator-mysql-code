<?php

final class PhabricatorDashboardQuery
  extends PhabricatorCursorPagedPolicyAwareQuery {

  private $ids;
  private $phids;
  private $statuses;

  private $needPanels;
  private $needProjects;

  public function withIDs(array $ids) {
    $this->ids = $ids;
    return $this;
  }

  public function withPHIDs(array $phids) {
    $this->phids = $phids;
    return $this;
  }

  public function withStatuses(array $statuses) {
    $this->statuses = $statuses;
    return $this;
  }

  public function needPanels($need_panels) {
    $this->needPanels = $need_panels;
    return $this;
  }

  public function needProjects($need_projects) {
    $this->needProjects = $need_projects;
    return $this;
  }

  protected function loadPage() {
    return $this->loadStandardPage($this->newResultObject());
  }

  public function newResultObject() {
    return new PhabricatorDashboard();
  }

  protected function didFilterPage(array $dashboards) {

    $phids = mpull($dashboards, 'getPHID');

    if ($this->needPanels) {
      $edge_query = id(new PhabricatorEdgeQuery())
        ->withSourcePHIDs($phids)
        ->withEdgeTypes(
          array(
            PhabricatorDashboardDashboardHasPanelEdgeType::EDGECONST,
          ));
      $edge_query->execute();

      $panel_phids = $edge_query->getDestinationPHIDs();
      if ($panel_phids) {
        $panels = id(new PhabricatorDashboardPanelQuery())
          ->setParentQuery($this)
          ->setViewer($this->getViewer())
          ->withPHIDs($panel_phids)
          ->execute();
        $panels = mpull($panels, null, 'getPHID');
      } else {
        $panels = array();
      }

      foreach ($dashboards as $dashboard) {
        $dashboard_phids = $edge_query->getDestinationPHIDs(
          array($dashboard->getPHID()));
        $dashboard_panels = array_select_keys($panels, $dashboard_phids);

        $dashboard->attachPanelPHIDs($dashboard_phids);
        $dashboard->attachPanels($dashboard_panels);
      }
    }

    if ($this->needProjects) {
      $edge_query = id(new PhabricatorEdgeQuery())
        ->withSourcePHIDs($phids)
        ->withEdgeTypes(
          array(
            PhabricatorProjectObjectHasProjectEdgeType::EDGECONST,
          ));
      $edge_query->execute();

      foreach ($dashboards as $dashboard) {
        $project_phids = $edge_query->getDestinationPHIDs(
          array($dashboard->getPHID()));
        $dashboard->attachProjectPHIDs($project_phids);
      }
    }

    return $dashboards;
  }

  protected function buildWhereClauseParts(AphrontDatabaseConnection $conn) {
    $where = parent::buildWhereClauseParts($conn);

    if ($this->ids !== null) {
      $where[] = qsprintf(
        $conn,
        'id IN (%Ld)',
        $this->ids);
    }

    if ($this->phids !== null) {
      $where[] = qsprintf(
        $conn,
        'phid IN (%Ls)',
        $this->phids);
    }

    if ($this->statuses !== null) {
      $where[] = qsprintf(
        $conn,
        'status IN (%Ls)',
        $this->statuses);
    }

    return $where;
  }

  public function getQueryApplicationClass() {
    return 'PhabricatorDashboardApplication';
  }

}
