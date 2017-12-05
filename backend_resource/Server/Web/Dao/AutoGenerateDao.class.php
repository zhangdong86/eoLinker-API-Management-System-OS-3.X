<?php

/**
 * @name eolinker open source，eolinker开源版本
 * @link https://www.eolinker.com
 * @package eolinker
 * @author www.eolinker.com 广州银云信息科技有限公司 ©2015-2016
 *  * eolinker，业内领先的Api接口管理及测试平台，为您提供最专业便捷的在线接口管理、测试、维护以及各类性能测试方案，帮助您高效开发、安全协作。
 * 如在使用的过程中有任何问题，欢迎加入用户讨论群进行反馈，我们将会以最快的速度，最好的服务态度为您解决问题。
 * 用户讨论QQ群：284421832
 *
 * 注意！eolinker开源版本仅供用户下载试用、学习和交流，禁止“一切公开使用于商业用途”或者“以eolinker开源版本为基础而开发的二次版本”在互联网上流通。
 * 注意！一经发现，我们将立刻启用法律程序进行维权。
 * 再次感谢您的使用，希望我们能够共同维护国内的互联网开源文明和正常商业秩序。
 *
 */
class AutoGenerateDao
{
    /**
     * 导入接口
     * @param $data array json格式数据
     * @param $project_id int 项目ID
     * @return bool
     */
    public function importApi(&$data, &$project_id)
    {
        $db = getDatabase();
        try {
            // 开始事务
            $db->beginTransaction();
            $db->prepareExecuteAll('DELETE FROM eo_api_header WHERE eo_api_header.apiID IN (SELECT eo_api.apiID FROM eo_api WHERE eo_api.projectID = ?);', array($project_id));
            $db->prepareExecuteAll('DELETE FROM eo_api_request_value WHERE eo_api_request_value.paramID IN (SELECT eo_api_request_param.paramID FROM eo_api_request_param LEFT JOIN eo_api ON eo_api_request_param.apiID = eo_api.apiID WHERE eo_api.projectID = ?);', array($project_id));
            $db->prepareExecuteAll('DELETE FROM eo_api_request_param WHERE eo_api_request_param.apiID IN (SELECT eo_api.apiID FROM eo_api WHERE eo_api.projectID = ?)', array($project_id));
            $db->prepareExecuteAll('DELETE FROM eo_api_result_value WHERE eo_api_result_value.paramID IN (SELECT eo_api_result_param.paramID FROM eo_api_result_param LEFT JOIN eo_api ON eo_api_result_param.apiID = eo_api.apiID WHERE eo_api.projectID = ?);', array($project_id));
            $db->prepareExecuteAll('DELETE FROM eo_api_result_param WHERE eo_api_result_param.apiID IN (SELECT eo_api.apiID FROM eo_api WHERE eo_api.projectID = ?)', array($project_id));
            $db->prepareExecuteAll('DELETE FROM eo_api_group WHERE eo_api_group.projectID = ?;', array($project_id));
            $db->prepareExecuteAll('DELETE FROM eo_api WHERE eo_api.projectID = ?;', array($project_id));
            $db->prepareExecuteAll('DELETE FROM eo_api_cache WHERE eo_api_cache.projectID = ?;', array($project_id));

            // 插入接口分组信息
            foreach ($data as $api_group) {
                $db->prepareExecute('INSERT INTO eo_api_group (eo_api_group.groupName,eo_api_group.projectID) VALUES (?,?);', array(
                    $api_group['groupName'],
                    $project_id
                ));

                if ($db->getAffectRow() < 1)
                    throw new \PDOException("addGroup error");

                $group_id = $db->getLastInsertID();
                if ($api_group['apiList']) {
                    foreach ($api_group['apiList'] as $api) {
                        // 插入api基本信息
                        $db->prepareExecute('INSERT INTO eo_api (eo_api.apiName,eo_api.apiURI,eo_api.apiProtocol,eo_api.apiSuccessMock,eo_api.apiFailureMock,eo_api.apiRequestType,eo_api.apiStatus,eo_api.groupID,eo_api.projectID,eo_api.starred,eo_api.apiNoteType,eo_api.apiNoteRaw,eo_api.apiNote,eo_api.apiRequestParamType,eo_api.apiRequestRaw,eo_api.apiUpdateTime) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?);', array(
                            $api['baseInfo']['apiName'],
                            $api['baseInfo']['apiURI'],
                            $api['baseInfo']['apiProtocol'],
                            $api['baseInfo']['apiSuccessMock'],
                            $api['baseInfo']['apiFailureMock'],
                            $api['baseInfo']['apiRequestType'],
                            $api['baseInfo']['apiStatus'],
                            $group_id,
                            $project_id,
                            $api['baseInfo']['starred'],
                            $api['baseInfo']['apiNoteType'],
                            $api['baseInfo']['apiNoteRaw'],
                            $api['baseInfo']['apiNote'],
                            $api['baseInfo']['apiRequestParamType'],
                            $api['baseInfo']['apiRequestRaw'],
                            $api['baseInfo']['apiUpdateTime']
                        ));

                        if ($db->getAffectRow() < 1)
                            throw new \PDOException("addApi error");

                        $api_id = $db->getLastInsertID();

                        if ($api['headerInfo']) {
                            // 插入header信息
                            foreach ($api['headerInfo'] as $header) {
                                $db->prepareExecute('INSERT INTO eo_api_header (eo_api_header.headerName,eo_api_header.headerValue,eo_api_header.apiID) VALUES (?,?,?);', array(
                                    $header['headerName'],
                                    $header['headerValue'],
                                    $api_id
                                ));

                                if ($db->getAffectRow() < 1)
                                    throw new \PDOException("addHeader error");
                            }
                        }

                        if ($api['requestInfo']) {
                            // 插入api请求值信息
                            foreach ($api['requestInfo'] as $request) {
                                $db->prepareExecute('INSERT INTO eo_api_request_param (eo_api_request_param.apiID,eo_api_request_param.paramName,eo_api_request_param.paramKey,eo_api_request_param.paramValue,eo_api_request_param.paramLimit,eo_api_request_param.paramNotNull,eo_api_request_param.paramType) VALUES (?,?,?,?,?,?,?);', array(
                                    $api_id,
                                    $request['paramName'],
                                    $request['paramKey'],
                                    $request['paramValue'],
                                    $request['paramLimit'],
                                    $request['paramNotNull'],
                                    $request['paramType']
                                ));

                                if ($db->getAffectRow() < 1)
                                    throw new \PDOException("addRequestParam error");

                                $param_id = $db->getLastInsertID();

                                if ($request['paramValueList']) {
                                    foreach ($request['paramValueList'] as $value) {
                                        $db->prepareExecute('INSERT INTO eo_api_request_value (eo_api_request_value.paramID,eo_api_request_value.`value`,eo_api_request_value.valueDescription) VALUES (?,?,?);', array(
                                            $param_id,
                                            $value['value'],
                                            $value['valueDescription']
                                        ));

                                        if ($db->getAffectRow() < 1)
                                            throw new \PDOException("addApi error");
                                    };
                                }
                            };
                        }

                        if ($api['resultInfo']) {
                            // 插入api返回值信息
                            foreach ($api['resultInfo'] as $result) {
                                $db->prepareExecute('INSERT INTO eo_api_result_param (eo_api_result_param.apiID,eo_api_result_param.paramName,eo_api_result_param.paramKey,eo_api_result_param.paramNotNull) VALUES (?,?,?,?);', array(
                                    $api_id,
                                    $result['paramName'],
                                    $result['paramKey'],
                                    $result['paramNotNull']
                                ));

                                if ($db->getAffectRow() < 1)
                                    throw new \PDOException("addResultParam error");

                                $param_id = $db->getLastInsertID();

                                if ($result['paramValueList']) {
                                    foreach ($result['paramValueList'] as $value) {
                                        $db->prepareExecute('INSERT INTO eo_api_result_value (eo_api_result_value.paramID,eo_api_result_value.`value`,eo_api_result_value.valueDescription) VALUES (?,?,?);;', array(
                                            $param_id,
                                            $value['value'],
                                            $value['valueDescription']
                                        ));

                                        if ($db->getAffectRow() < 1)
                                            throw new \PDOException("addApi error");
                                    };
                                }
                            };
                        }


                        // 插入api缓存数据用于导出
                        $db->prepareExecute("INSERT INTO eo_api_cache (eo_api_cache.projectID,eo_api_cache.groupID,eo_api_cache.apiID,eo_api_cache.apiJson,eo_api_cache.starred) VALUES (?,?,?,?,?);", array(
                            $project_id,
                            $group_id,
                            $api_id,
                            json_encode($api),
                            $api['baseInfo']['starred']
                        ));

                        if ($db->getAffectRow() < 1) {
                            throw new \PDOException("addApiCache error");
                        }
                    }
                }
                if (is_array($api_group['apiGroupChildList'])) {
                    // 二级分组代码

                    $group_parent_id = $group_id;
                    foreach ($api_group['apiGroupChildList'] as $api_group_child) {
                        $db->prepareExecute('INSERT INTO eo_api_group (eo_api_group.groupName,eo_api_group.projectID,eo_api_group.parentGroupID, eo_api_group.isChild) VALUES (?,?,?,?);', array(
                            $api_group_child['groupName'],
                            $project_id,
                            $group_parent_id,
                            1
                        ));

                        if ($db->getAffectRow() < 1)
                            throw new \PDOException("addChildGroup error");

                        $group_id = $db->getLastInsertID();

                        // 如果当前分组没有接口，则跳过到下一分组
                        if (empty($api_group_child['apiList']))
                            continue;

                        foreach ($api_group_child['apiList'] as $api) {
                            // 插入api基本信息
                            $db->prepareExecute('INSERT INTO eo_api (eo_api.apiName,eo_api.apiURI,eo_api.apiProtocol,eo_api.apiSuccessMock,eo_api.apiFailureMock,eo_api.apiRequestType,eo_api.apiStatus,eo_api.groupID,eo_api.projectID,eo_api.starred,eo_api.apiNoteType,eo_api.apiNoteRaw,eo_api.apiNote,eo_api.apiRequestParamType,eo_api.apiRequestRaw,eo_api.apiUpdateTime) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?);', array(
                                $api['baseInfo']['apiName'],
                                $api['baseInfo']['apiURI'],
                                $api['baseInfo']['apiProtocol'],
                                $api['baseInfo']['apiSuccessMock'],
                                $api['baseInfo']['apiFailureMock'],
                                $api['baseInfo']['apiRequestType'],
                                $api['baseInfo']['apiStatus'],
                                $group_id,
                                $project_id,
                                $api['baseInfo']['starred'],
                                $api['baseInfo']['apiNoteType'],
                                $api['baseInfo']['apiNoteRaw'],
                                $api['baseInfo']['apiNote'],
                                $api['baseInfo']['apiRequestParamType'],
                                $api['baseInfo']['apiRequestRaw'],
                                $api['baseInfo']['apiUpdateTime']
                            ));

                            if ($db->getAffectRow() < 1)
                                throw new \PDOException("addChildApi error");

                            $api_id = $db->getLastInsertID();

                            if ($api['headerInfo']) {
                                // 插入header信息
                                foreach ($api['headerInfo'] as $header) {
                                    $db->prepareExecute('INSERT INTO eo_api_header (eo_api_header.headerName,eo_api_header.headerValue,eo_api_header.apiID) VALUES (?,?,?);', array(
                                        $header['headerName'],
                                        $header['headerValue'],
                                        $api_id
                                    ));

                                    if ($db->getAffectRow() < 1)
                                        throw new \PDOException("addChildHeader error");
                                }
                            }

                            if ($api['requestInfo']) {
                                // 插入api请求值信息
                                foreach ($api['requestInfo'] as $request) {
                                    $db->prepareExecute('INSERT INTO eo_api_request_param (eo_api_request_param.apiID,eo_api_request_param.paramName,eo_api_request_param.paramKey,eo_api_request_param.paramValue,eo_api_request_param.paramLimit,eo_api_request_param.paramNotNull,eo_api_request_param.paramType) VALUES (?,?,?,?,?,?,?);', array(
                                        $api_id,
                                        $request['paramName'],
                                        $request['paramKey'],
                                        $request['paramValue'],
                                        $request['paramLimit'],
                                        $request['paramNotNull'],
                                        $request['paramType']
                                    ));

                                    if ($db->getAffectRow() < 1)
                                        throw new \PDOException("addChildRequestParam error");

                                    $param_id = $db->getLastInsertID();

                                    if ($request['paramValueList']) {
                                        foreach ($request['paramValueList'] as $value) {
                                            $db->prepareExecute('INSERT INTO eo_api_request_value (eo_api_request_value.paramID,eo_api_request_value.`value`,eo_api_request_value.valueDescription) VALUES (?,?,?);', array(
                                                $param_id,
                                                $value['value'],
                                                $value['valueDescription']
                                            ));

                                            if ($db->getAffectRow() < 1)
                                                throw new \PDOException("addChildApi error");
                                        };
                                    }
                                };
                            }

                            if ($api['resultInfo']) {
                                // 插入api返回值信息
                                foreach ($api['resultInfo'] as $result) {
                                    $db->prepareExecute('INSERT INTO eo_api_result_param (eo_api_result_param.apiID,eo_api_result_param.paramName,eo_api_result_param.paramKey,eo_api_result_param.paramNotNull) VALUES (?,?,?,?);', array(
                                        $api_id,
                                        $result['paramName'],
                                        $result['paramKey'],
                                        $result['paramNotNull']
                                    ));

                                    if ($db->getAffectRow() < 1)
                                        throw new \PDOException("addChildResultParam error");

                                    $param_id = $db->getLastInsertID();

                                    if ($result['paramValueList']) {
                                        foreach ($result['paramValueList'] as $value) {
                                            $db->prepareExecute('INSERT INTO eo_api_result_value (eo_api_result_value.paramID,eo_api_result_value.`value`,eo_api_result_value.valueDescription) VALUES (?,?,?);;', array(
                                                $param_id,
                                                $value['value'],
                                                $value['valueDescription']
                                            ));

                                            if ($db->getAffectRow() < 1)
                                                throw new \PDOException("addChildParamValue error");
                                        };
                                    }
                                };
                            }

                            // 插入api缓存数据用于导出
                            $db->prepareExecute("INSERT INTO eo_api_cache (eo_api_cache.projectID,eo_api_cache.groupID,eo_api_cache.apiID,eo_api_cache.apiJson,eo_api_cache.starred) VALUES (?,?,?,?,?);", array(
                                $project_id,
                                $group_id,
                                $api_id,
                                json_encode($api),
                                $api['baseInfo']['starred']
                            ));

                            if ($db->getAffectRow() < 1) {
                                throw new \PDOException("addChildApiCache error");
                            }
                        }

                    }
                }
            }
        } catch (\PDOException $e) {
            $db->rollBack();
            return FALSE;
        }
        $db->commit();
        return TRUE;
    }
}