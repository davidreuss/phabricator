<?php

/*
 * Copyright 2011 Facebook, Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

abstract class DiffusionDiffQuery {

  private $request;
  protected $renderingReference;

  final private function __construct() {
    // <private>
  }

  final public static function newFromDiffusionRequest(
    DiffusionRequest $request) {

    $repository = $request->getRepository();

    switch ($repository->getVersionControlSystem()) {
      case PhabricatorRepositoryType::REPOSITORY_TYPE_GIT:
        $class = 'DiffusionGitDiffQuery';
        break;
      case PhabricatorRepositoryType::REPOSITORY_TYPE_SVN:
        $class = 'DiffusionSvnDiffQuery';
        break;
      default:
        throw new Exception("Unsupported VCS!");
    }

    PhutilSymbolLoader::loadClass($class);
    $query = new $class();

    $query->request = $request;

    return $query;
  }

  final protected function getRequest() {
    return $this->request;
  }

  final public function getRenderingReference() {
    return $this->renderingReference;
  }

  final public function loadChangeset() {
    return $this->executeQuery();
  }

  abstract protected function executeQuery();

  protected function getEffectiveCommit() {
    $drequest = $this->getRequest();

    $modified_query = DiffusionLastModifiedQuery::newFromDiffusionRequest(
      $drequest);
    list($commit) = $modified_query->loadLastModification();
    if (!$commit) {
      // TODO: Improve error messages here.
      return null;
    }
    return $commit->getCommitIdentifier();
  }

}
