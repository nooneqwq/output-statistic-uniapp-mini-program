<template>
  <view class="content">
    <image class="logo" src="/static/logo.jpg" mode="aspectFill"></image>
    <view class="text-area">
      <text class="title">{{ title }}</text>
    </view>
  </view>
</template>

<script>
export default {
  data() {
    return {
      title: "欢迎",
    };
  },
  onLoad() {
    this.verify();
  },
  onShow() {
    this.verify();
  },
  methods: {
    verify() {
      //调试代码，用于清除缓存以模拟首次登录
      /*try {
					    uni.clearStorageSync();
					} catch (e) {
					    // error
					}*/
      //sign为首次登录的标志位，0为首次登录，1为第二次登录
      try {
        const value = uni.getStorageSync("sign");
        if (value) {
          //console.log(value)
          //realPassWord为自定义密码，用于身份标识
          try {
            const value = uni.getStorageSync("realPassWord");
            if (value) {
              //console.log(value);
              //verify函数用于验证用户是否存在于已第一次登录的用户列表中
              uni.request({
                url: "http://127.0.0.1:8899/api/verify",
                method: "POST",
                data: {
                  code: value,
                },
                success: (res1) => {
                  //若返回code为0，说明用户不在数据库中，跳转至user页面进行登录
                  if (res1.data.code == 0) {
                    uni.switchTab({
                      url: "/pages/user/user",
                    });
                    //console.log("verify到达")
                  }
                  //否则用户已不是第一次登录，直接跳转至数据页面进行数据填写
                  else {
                    uni.switchTab({
                      url: "/pages/statistic/statistic",
                    });

                    //console.log("verify到达")
                  }
                },
              });
            }
          } catch (e) {
            // error
          }
        }
        //若没有取到标志位sign,跳转到登录页
        else {
          uni.switchTab({
            url: "/pages/user/user",
          });
          console.log("外层到达");
        }
      } catch (e) {
        uni.switchTab({
          url: "/pages/user/user",
        });
      }
    },
  },
};
</script>

<style>
.content {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
}

.logo {
  height: 200rpx;
  width: 200rpx;
  margin-top: 200rpx;
  margin-left: auto;
  margin-right: auto;
  margin-bottom: 50rpx;
}

.text-area {
  display: flex;
  justify-content: center;
}

.title {
  font-size: 36rpx;
  color: #8f8f94;
}
</style>
