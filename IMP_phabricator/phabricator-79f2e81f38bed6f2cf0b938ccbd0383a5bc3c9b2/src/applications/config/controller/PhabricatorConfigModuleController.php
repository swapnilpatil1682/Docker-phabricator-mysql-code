<?php

final class PhabricatorConfigModuleController
  extends PhabricatorConfigController {

  public function handleRequest(AphrontRequest $request) {
    $viewer = $this->getViewer();
    $key = $request->getURIData('module');

    $all_modules = PhabricatorConfigModule::getAllModules();
    if (empty($all_modules[$key])) {
      return new Aphront404Response();
    }

    $module = $all_modules[$key];
    $content = $module->renderModuleStatus($request);
    $name = $module->getModuleName();

    $crumbs = $this->buildApplicationCrumbs();
    $crumbs->addTextCrumb($name);

    $nav = $this->buildSideNavView();
    $nav->selectFilter('module/'.$key.'/');
    $nav->appendChild(
      array(
        $crumbs,
        $content,
      ));

    return $this->buildApplicationPage(
      $nav,
      array(
        'title' => $name,
      ));
  }

}
