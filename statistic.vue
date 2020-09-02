<template>
  <!-- -->
  <view>
    <text class="uni-form-item__title">{{ text0 }}</text>
    <input
      class="uni-input-wrapper"
      @input="onKeyInput1"
      type="number"
      maxlength="8"
      placeholder-style="color:#009143"
      placeholder="卷烟机产量（支）"
      bindinput="input"
    />
    <input
      class="uni-input-wrapper"
      @input="onKeyInput2"
      type="number"
      maxlength="6"
      placeholder-style="color:#009143"
      placeholder="卷烟机剔烟数（支）"
    />
    <input
      class="uni-input-wrapper"
      @input="onKeyInput3"
      type="number"
      maxlength="5"
      placeholder-style="color:#009143"
      placeholder="卷烟机空头剔烟数（支）"
    />
    <input
      class="uni-input-wrapper"
      @input="onKeyInput4"
      type="number"
      maxlength="3"
      placeholder-style="color:#009143"
      placeholder="卷烟机停机次数（次）"
    />
    <input
      class="uni-input-wrapper"
      type="digits"
      @input="onKeyInput5"
      maxlength="3"
      placeholder-style="color:#009143"
      placeholder="烟支回收盘数（盘）"
    />
    <text>{{ text1 }}</text>
    <input
      class="uni-input-wrapper"
      @input="onKeyInput6"
      type="number"
      maxlength="7"
      placeholder-style="color:#009143"
      placeholder="小包机产量（包）"
    />
    <input
      class="uni-input-wrapper"
      @input="onKeyInput7"
      type="number"
      maxlength="5"
      placeholder-style="color:#009143"
      placeholder="小包机剔除数（包）"
    />
    <input
      class="uni-input-wrapper"
      @input="onKeyInput8"
      type="number"
      maxlength="5"
      placeholder-style="color:#009143"
      placeholder="小包机空头剔除数（包）"
    />
    <input
      class="uni-input-wrapper"
      @input="onKeyInput9"
      type="number"
      maxlength="3"
      placeholder-style="color:#009143"
      placeholder="油封机剔烟数（包）"
    />
    <input
      class="uni-input-wrapper"
      @input="onKeyInput10"
      type="number"
      maxlength="3"
      placeholder-style="color:#009143"
      placeholder="小包机停机次数（次）"
    />
    <!-- 输入框上方实时显示输入值-->
    <text class="uni-form-item__title">提升器产量：{{ totalOutput }} 条</text>
    <input
      class="uni-input-wrapper"
      type="number"
      @input="onKeyInput"
      maxlength="5"
      placeholder-style="color:#009143"
      placeholder="提升器产量(条)"
    />
    <button class=".button-sp-area" @click="updatedata" type="primary">
      提交产量
    </button>
  </view>
</template>

<script>
export default {
  data() {
    return {
      text0: "卷烟机产量",
      text1: "包装机产量",
      mNumber: 0,
      text3: "条",
      title: "数据统计",
      totalOutput: 0,
      cmmOutput: 0,
      cmmReject: 0,
      cmmDefectNum: 0,
      cmmStopNum: 0,
      cigRecPanls: 0,
      sPackingOutput: 0,
      sPackingReject: 0,
      sPackingDefectNum: 0,
      sealingMRejectNum: 0,
      sPackingStopNum: 0,
      msg: "请求上传数据失败，请联系管理员",
    };
  },
  onLoad() {
    //updatedata();
  },
  methods: {
    //上传数据
    updatedata() {
      //获取缓存中的机器号
      try {
        const value = uni.getStorageSync("mNumber");
        if (value) {
          this.mNumber = value;
          console.log(value);
        }
      } catch (e) {}
      let _this = this;
      var content0 = "请确认产量为";
      var content1 = String(_this.totalOutput);
      var content2 = "条";
      var content = content0 + content1 + content2;
      //console.log(content);
      //显示输入产量，确认提交
      uni.showModal({
        title: "确认",
        confirmText: "确定提交",
        confirmColor: "#009143",
        content: content,
        success: function (res) {
          if (res.confirm) {
            uni.request({
              url: "http://127.0.0.1:8899/api/updatedata",
              method: "POST",
              data: {
                totalOutput: _this.totalOutput,
                cmmOutput: _this.cmmOutput,
                cmmReject: _this.cmmReject,
                cmmDefectNum: _this.cmmDefectNum,
                cmmStopNum: _this.cmmStopNum,
                cigRecPanl: _this.cigRecPanls,
                sPackingOutput: _this.sPackingOutput,
                sPackingReject: _this.sPackingReject,
                sPackingDefectNum: _this.sPackingDefectNum,
                sealingMRejectNum: _this.sealingMRejectNum,
                sPackingStopNum: _this.sPackingStopNum,
                mNumber: _this.mNumber,
              },
              success: (res) => {
                console.log(res.data.msg);
                //显示从服务端返回的成功信息
                uni.showToast({
                  title: res.data.msg,
                });
              },
              fail: () => {
                uni.showToast({
                  title: msg,
                });
              },
              complete: () => {
                //根据当前时间判断班次
                let date = new Date();
                let hours = date.getHours();
                //let  newDate=date.toLocaleString('chinese', { hour12: false });   //获取24小时制，中国时间
                //console.log(date.getHours());
                if (hours > 5 && hours < 13) {
                  var shift = 0;
                } else if (hours > 12 && hours < 19) {
                  var shift = 1;
                } else if (hours > 18 || hours < 6) {
                  var shift = 2;
                }
                //根据是否周一判断是否为周保养
                function getWeekDate() {
                  var now = new Date();
                  var day = now.getDay();
                  var weeks = new Array(
                    "星期日",
                    "星期一",
                    "星期二",
                    "星期三",
                    "星期四",
                    "星期五",
                    "星期六"
                  );
                  var week = weeks[day];
                  return week;
                }
                let weekinfo = getWeekDate();
                var productionTime;
                if (weekinfo == "星期一" && shift == 0) {
                  productionTime = 3.5; //早班且周保养制度时间为3.5小时
                } else if (shift == 0) {
                  productionTime = 4.5; //早班且非周保养制度时间为4。5小时
                }
                if (shift == 1 || shift == 2) {
                  productionTime = 5.5; //中班或晚班制度时间为5.5小时
                }
                //额定小时产量
                var ratedOutputPerBox = new Array(
                  16.8,
                  16.8,
                  16.8,
                  16.8,
                  16.8,
                  14.4,
                  14.4,
                  14.4,
                  19.2,
                  19.2,
                  19.2,
                  19.2,
                  19.2,
                  16.8,
                  16.8,
                  7.2,
                  7.2,
                  7.2,
                  7.2
                );
                //获取机器号数组下标, 用于获取不同机型的额定小时产量;
                try {
                  const value = uni.getStorageSync("index");
                  //console.log(value);
                  var currentROPB = ratedOutputPerBox[value];
                } catch (e) {}
                //净效率计算，保留两位小数
                var netEfficiency = (
                  (100 * _this.totalOutput) /
                  250 /
                  productionTime /
                  currentROPB
                ).toFixed(2);

                var content1 = "净效率约为" + netEfficiency + "%";
                uni.showModal({
                  title: "净效率",
                  confirmColor: "#009143",
                  content: content1,
                });
              },
            });
          } else if (res.cancel) {
            uni.showToast({
              title: "请重新填写",
              image: "../../static/reload.png",
            });
          }
        },
      });
    },
    //console.log("click detect")
    //console.log(this.totalOutput)

    onKeyInput: function (event) {
      this.totalOutput = event.detail.value;
    },
    onKeyInput1: function (event) {
      this.cmmOutput = event.detail.value;
    },
    onKeyInput2: function (event) {
      this.cmmReject = event.detail.value;
    },
    onKeyInput3: function (event) {
      this.cmmDefectNum = event.detail.value;
    },
    onKeyInput4: function (event) {
      this.cmmStopNum = event.detail.value;
    },
    onKeyInput5: function (event) {
      this.cigRecPanls = event.detail.value;
    },
    onKeyInput6: function (event) {
      this.sPackingOutput = event.detail.value;
    },
    onKeyInput7: function (event) {
      this.sPackingReject = event.detail.value;
    },
    onKeyInput8: function (event) {
      this.sPackingDefectNum = event.detail.value;
    },
    onKeyInput9: function (event) {
      this.sealingMRejectNum = event.detail.value;
    },
    onKeyInput10: function (event) {
      this.sPackingStopNum = event.detail.value;
    },
  },
};
</script>

<style>
.uni-form-item__title {
  font-size: 16px;
  line-height: 24px;
}

.uni-input-wrapper {
  display: flex;
  padding: 8px 13px;
  flex-direction: row;
  flex-wrap: nowrap;
  background-color: #ffffff;
}

.uni-input {
  height: 28px;
  line-height: 28px;
  font-size: 15px;
  padding: 0px;
  flex: 1;
  background-color: #ffffff;
}

input {
  border: 1px solid #22cf22;
  width: 61.8%;
  margin: 6.18%;
  padding: 5px;
}
.button-sp-area {
  margin: 0 auto;
  width: 61.8%;
}
</style>
