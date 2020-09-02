<template>
  <view class="main">
    <!-- 如果只是展示用户头像昵称，可以使用 <open-data /> 组件 -->
    <text v-if="nickName">Hello: {{ nickName }}</text>
    <button
      @click="clickHandle"
      v-if="!nickName"
      open-type="getUserInfo"
      @getuserinfo="getUserInfo"
      bindgetuserinfo="getUserInfo"
    >
      授权登录
    </button>

    <view>请输入工号(六位，前缀10)</view>
    <input type="number" @input="onKeyInput" maxlength="6" placeholder="工号" />

    <picker @change="bindPickerChange" :value="index" :range="array">
      <view>点击此处选择机台号{{ array[index] }}#</view>
    </picker>
  </view>
</template>

<script>
export default {
  onLoad() {
    this.dologin();
  },
  data() {
    return {
      nickName: null,
      //机器号数组
      array: [
        "1",
        "2",
        "3",
        "4",
        "5",
        "6",
        "7",
        "8",
        "11",
        "12",
        "13",
        "14",
        "15",
        "56",
        "57",
        "62",
        "63",
        "64",
        "65",
      ],
      //工号和机器号的字典
      wokerdic: {
        "109317": "1",
        "109318": "1",
        "109319": "1",
        "109320": "2",
        "109321": "2",
        "109322": "2",
        "109323": "8",
        "109324": "11",
        "109325": "56",
        "109326": "62",
      },
      //数组下标
      index: 0,
      //工号
      inputValue: 0,
      msg: "机台号错误",
    };
  },
  methods: {
    getUserInfo() {
      let _this = this;
      // 查看是否授权
      wx.getSetting({
        success(res) {
          if (res.authSetting["scope.userInfo"]) {
            // 已经授权，可以直接调用 getUserInfo 获取头像昵称
            wx.getUserInfo({
              success: (res) => {
                //console.log(res)
                if (res.userInfo) _this.nickName = res.userInfo.nickName;
              },
            });
          }
        },
      });
    },
    dologin() {
      let _this = this;
      uni.login({
        provider: "weixin",
        success: (res) => {
          if (res.code) {
            uni.request({
              url: "http://127.0.0.1:8899/api/login",
              method: "POST",
              data: {
                code: res.code,
              },
              success: (res1) => {
                //console.log(res1)
                if (res1.data.code !== 0) {
                  return uni.showToast({
                    title: res1.data.msg,
                  });
                }
                let data = res1.data.data;
                if (data.openid) {
                  //console.log("SUCCESS!!!");
                  //存储自定义密码到缓存
                  try {
                    console.log("set realPassWord");
                    uni.setStorageSync("realPassWord", data.passWord);
                  } catch (e) {
                    // error
                  }
                }
              },
              fail: () => {},
              complete: () => {},
            });
          }
        },
      });
    },

    clickHandle() {
      let _this = this;
      console.log("click detect");
    },
    bindPickerChange: function (e) {
      console.log("picker发送选择改变，携带值为", e.target.value);
      this.index = e.target.value;
      //存储用户选择到机器号
      try {
        uni.setStorageSync("mNumber", this.array[this.index]);
      } catch (e) {
        // error
      }
      //存储机器号数组的下标
      try {
        uni.setStorageSync("index", this.index);
      } catch (e) {
        // error
      }
      //this.dologin();
      let _this = this;
      //检查工号是否正确的函数，由于机器号不是连续的，不同区间的机器号需要加上或减去不同的数值以匹配机器号数组连续的下标
      function checkWorkNumber(para) {
        if (_this.wokerdic[_this.inputValue] - para == _this.index) {
          console.log("check work number success");
          uni.request({
            url: "http://127.0.0.1:8899/api/update",
            method: "POST",
            data: {
              code: _this.wokerdic[_this.inputValue],
            },
            success: (res1) => {
              console.log(res1.data.data.machineNumber);
              //存储机器号
              try {
                //console.log(res1)
                uni.setStorageSync("mNumber", res1.data.data.machineNumber);
              } catch (e) {
                // error
              }
              //存储标志位
              try {
                uni.setStorageSync("sign", 1);
                console.log("set sign");
              } catch (e) {
                // error
              }
              //跳转到index 然后跳转到数据页
              uni.switchTab({
                url: "/pages/index/index",
              });
            },
          });
        }
        //工号与机器号不匹配，弹出机器号错误提示
        else {
          console.log("fail");
          return uni.showToast({
            image: "../../static/fail.png",
            title: _this.msg,
          });
        }
      }
      //输入的工号不存在于工号字典键值列表中或少于6位
      if (!_this.wokerdic.hasOwnProperty(_this.inputValue)) {
        //输入的工号不足六位
        if (_this.inputValue < 9999) {
          uni.showModal({
            title: "提示",
            content: "请输入六位工号，前缀10",
            confirmText: "修改输入",
            confirmColor: "#009143",
            success: function (res) {
              if (res.confirm) {
                console.log("用户点击确定");
              } else if (res.cancel) {
                console.log("用户点击取消");
              }
            },
          });
        }
        //工号不在列表中的情况
        else {
          uni.showModal({
            title: "提示",
            content: "工号不在列表中，请联系管理员",
            confirmColor: "#009143",
            success: function (res) {
              if (res.confirm) {
                console.log("用户点击确定");
              } else if (res.cancel) {
                console.log("用户点击取消");
              }
            },
          });
        }
      }
      //para的作用是将不连续的机器号与连续的数组进行匹配的参数
      var para = 0;
      console.log(_this.wokerdic[_this.inputValue]);
      if (_this.wokerdic[_this.inputValue] < 9) {
        para = 1;
        checkWorkNumber(para);
      } else if (_this.wokerdic[_this.inputValue] < 16) {
        console.log(_this.index);
        para = 3;
        checkWorkNumber(para);
      } else if (_this.wokerdic[_this.inputValue] < 58) {
        para = 43;
        checkWorkNumber(para);
      } else if (_this.wokerdic[_this.inputValue] < 66) {
        para = 47;
        checkWorkNumber(para);
      }
    },
    //获取输入的工号
    onKeyInput: function (e) {
      this.inputValue = e.target.value;
    },
  },
};
</script>

<style>
.main {
  padding-top: 140px;
  text-align: center;
}
</style>
