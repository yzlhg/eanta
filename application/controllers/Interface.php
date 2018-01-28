<?php
class InterfaceController extends Controller {

    public function UserTokenAction(){
        $params = $this->getRequest()->getQuery();
        $context = json_decode($params['context'],true);
        if($context['FUserName'] == 'test' && $context['PassWord'] == 'test'){
            $token['RESULT'] = '00';
            $token['DESCR'] = md5($context['FUserName'].$context['PassWord']);
        } else {
            $token['RESULT'] = '01';
            $token['DESCR'] = '获取token失败！';
        }
        echo  json_encode($token);
        return false;
    }

    public function FindCardAmountAction(){
        $params = $this->getRequest()->getQuery();
        $context = json_decode($params['context'],true);
        if($context['ClientNumber'] == 'test' && $context['CardNumber'] == 'test' && $context['PassWord'] == 'test'){
            $return['RESULT'] = '00';
            $return['DESCR'] = '单据处理成功！';
        } else {
            $return['RESULT'] = '01';
            $return['DESCR'] = '单据处理失败！';
        }
        echo  json_encode($return);
        return false;
    }

    public function SaveCardAmountAction(){
        $params = $this->getRequest()->getQuery();
        $context = json_decode($params['context'],true);
        if($context['ClientNumber'] == '18796613336' && $context['CardNumber'] == 'test' && $context['PassWord'] == 'test'){
            $FSumAmount = $this->db->sql("UPDATE ICVIP SET FSumAmount=FSumAmount+100 WHERE FMobile='18796613336'")->affectedRows();
            $FAmtIn = $this->db->sql("UPDATE IC_VIPSoursePoints SET FAmtIn=FAmtIn+100 WHERE FMobile='18796613336'")->affectedRows();
            $return['RESULT'] = '00';
            $return['DESCR'] = '单据处理成功！';
        } else {
            $return['RESULT'] = '01';
            $return['DESCR'] = '单据处理失败！';
        }
        echo  json_encode($return);
        return false;
    }


}