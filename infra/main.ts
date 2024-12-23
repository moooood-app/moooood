import { Construct } from "constructs";
import { App, TerraformStack, TerraformVariable } from "cdktf";
import { AwsProvider } from "@cdktf/provider-aws/lib/provider";
import { NetworkStack } from "./stacks/NetworkStack";
import EncryptionStack from "./stacks/EncryptionStack";
import SecretsStack from "./stacks/SecretsStack";
import RegistryStack from "./stacks/RegistryStack";
import ClusterStack from "./stacks/ClusterStack";
import { DatabaseStack } from "./stacks/DatabaseStack";

class MooooodStack extends TerraformStack {
  constructor(scope: Construct, id: string) {
    super(scope, id);

    const version = new TerraformVariable(this, "version", {
      type: "string",
      description: "Version of the application using SemVer",
    });

    const region = "us-west-2";

    new AwsProvider(this, "AWS", {
      region,
    });

    const network = new NetworkStack(this, "network-stack", { region });
    const encryption = new EncryptionStack(this, "encryption-stack", { region });
    const secrets = new SecretsStack(this, "secrets-stack", { region, kmsKey: encryption.kmsKey });
    new DatabaseStack(this, "database-stack", {
      region, 
      privateSubnets: [network.privateSubnet],
      databaseSecurityGroup: network.databaseSecurityGroup,
      rdsMasterSecret: secrets.rdsMasterCredentials,
      kmsAlias: encryption.kmsKeyAlias,
    });
    const registry = new RegistryStack(this, "registry-stack", { region });
    new ClusterStack(app, "cluster-stack", {
      region,
      publicSubnet: network.publicSubnet,
      privateSubnet: network.privateSubnet,
      apiRepository: registry.apiRepository,
      apiSecurityGroup: network.apiSecurityGroup,
      databaseSecurityGroup: network.databaseSecurityGroup,
      version: version.value,
    });
  }
}

const app = new App();
new MooooodStack(app, "moooood-stack");
app.synth();
