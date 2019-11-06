### Slack Notification
A Wordpress Plugin that Notifies the number of yesterday's total New and Updated posts from each Sites and Users

###### On Wordpress
1. Clone the project inside wp-content/plugins
2. Enable the plugin `Slack Notification`

###### On Slack
1. [Create Slack App](https://api.slack.com/messaging/webhooks)
![image](https://user-images.githubusercontent.com/50949760/68267296-ea077480-008c-11ea-9405-2b4b512e8abc.png)
2. Create App Name and Select Slack Channel
![image](https://user-images.githubusercontent.com/50949760/68267353-1f13c700-008d-11ea-8266-9444da74b9e7.png)
3. Select Incoming Webhooks
![image](https://user-images.githubusercontent.com/50949760/68267424-5d10eb00-008d-11ea-99bb-7037988d8e6f.png)
4. Activate Incoming Webhooks and Add New Webhook to Workspace
![image](https://user-images.githubusercontent.com/50949760/68267533-b1b46600-008d-11ea-9a97-456b8c134f80.png)
5. Select A Channel on where you want to notify
![image](https://user-images.githubusercontent.com/50949760/68267565-d14b8e80-008d-11ea-9920-c8956a767ee4.png)
6. Copy and use this webhook
![image](https://user-images.githubusercontent.com/50949760/68267728-62bb0080-008e-11ea-8863-3d522ead0af3.png)
7. Paste Your `WEBHOOK_ENDPOINT`
8. You can generate Your own `ACCESS_TOKEN` its just a random text so that no one can trigger the notification
9. You can create another app for your testing notification and copy its `TEST_WEBHOOK_ENDPOINT`
![image](https://user-images.githubusercontent.com/50949760/68267876-e4129300-008e-11ea-891b-be12ea5db267.png)

###### Triggering the Notification with Insomnia
1. use Get Method with url : localhost/wp-json/_notify_slack
2. For Headers
  - `X-ACCESS-TOKEN` your generated token (its just a random text pls create your own)
  - `TEST` you can assign test if you want to notify it on test channel if it is set to `TRUE`

###### Output
I dont have any new and updated posts from yesterday that is why all are zero and no users displayed
![image](https://user-images.githubusercontent.com/50949760/68268469-45d3fc80-0091-11ea-91fb-a25cf752873f.png)
